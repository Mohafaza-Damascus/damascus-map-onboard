<?php

namespace App\Models;

use App\Support\SearchIndex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Street extends Model
{
    use HasFactory;

    protected $table = 'streets';

    /** تسميات أنواع الطرق بنتائج البحث (HIGHWAY_LABELS بـ Django) */
    public const HIGHWAY_LABELS = [
        'motorway' => 'أوتوستراد سريع',
        'trunk' => 'محور شرياني رئيسي',
        'trunk_link' => 'وصلة محورية',
        'primary' => 'شارع رئيسي',
        'secondary' => 'شارع فرعي رابط',
        'tertiary' => 'شارع محلي',
        'residential' => 'شارع سكني',
        'pedestrian' => 'شارع مشاة / سوق',
        'service' => 'طريق خدمي',
    ];

    protected $fillable = [
        'name_ar',
        'name_en',
        'highway_type',
        'admin_area_id',
        'center_lat',
        'center_lon',
        'bbox',
        'geometry',
        'search_text',
        'is_active',
    ];

    protected $casts = [
        'center_lat' => 'float',
        'center_lon' => 'float',
        'bbox' => 'array',
        'geometry' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // نفس save() بـ Django: بيحسب search_text قبل كل حفظ
        static::saving(function (Street $street) {
            // إذا تغيّر الحي والعلاقة محمّلة من قبل، لا نستخدم الاسم القديم
            if ($street->isDirty('admin_area_id')) {
                $street->unsetRelation('adminArea');
            }

            $street->search_text = static::searchTextFor(
                $street->name_ar,
                $street->name_en,
                $street->adminArea?->name_ar
            );
        });
    }

    /**
     * نص البحث المعاير. بمكان واحد حتى يستخدمو الـ hook والـ Seeder
     * (الـ Seeder بيحسبو بنفسو لأنو أحداث الموديل بتتعطّل مع WithoutModelEvents).
     */
    public static function searchTextFor(string $nameAr, ?string $nameEn, ?string $areaName): string
    {
        return SearchIndex::build($nameAr, $nameEn, $areaName ?? '');
    }

    public function adminArea(): BelongsTo
    {
        return $this->belongsTo(AdminArea::class, 'admin_area_id');
    }

    /** الشوارع الفعّالة فقط (is_active=True) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * فلتر البحث (q_street بـ search_all): search_text لكل المشتقات + الاسم الإنجليزي.
     * الاستخدام: Street::active()->matching($q, SearchIndex::variants($q))
     */
    public function scopeMatching(Builder $query, string $term, array $variants): Builder
    {
        $like = fn (string $s) => '%'.addcslashes($s, '\\%_').'%';

        return $query->where(function (Builder $w) use ($term, $variants, $like) {
            foreach ($variants as $v) {
                $w->orWhere('search_text', 'ilike', $like($v));
            }
            $w->orWhere('name_en', 'ilike', $like($term));
        });
    }

    /** highway_type أو 'residential' إذا فاضي (نفس `street.highway_type or 'residential'`) */
    public function highwayOrDefault(): string
    {
        return $this->highway_type ?: 'residential';
    }

    /** تصنيف الطريق للخريطة: minor / path / أو نوع الطريق نفسه بدون _link */
    public function roadClass(): string
    {
        $highway = $this->highwayOrDefault();
        $road = str_ends_with($highway, '_link') ? substr($highway, 0, -5) : $highway;

        return match (true) {
            in_array($road, ['residential', 'unclassified', 'living_street', 'road'], true) => 'minor',
            in_array($road, ['footway', 'cycleway', 'pedestrian', 'steps', 'bridleway'], true) => 'path',
            default => $road,
        };
    }

    /** وصلة (ramp) إذا انتهى النوع بـ _link */
    public function isRamp(): bool
    {
        return str_ends_with($this->highwayOrDefault(), '_link');
    }
}
