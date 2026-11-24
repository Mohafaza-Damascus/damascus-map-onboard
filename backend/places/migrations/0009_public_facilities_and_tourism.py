from django.db import migrations


def reorganize_facilities(apps, schema_editor):
    db = schema_editor.connection.alias
    categories = apps.get_model('places', 'Category').objects.using(db)
    subs = apps.get_model('places', 'SubCategory').objects.using(db)
    places = apps.get_model('places', 'Place').objects.using(db)
    public = categories.filter(slug='public-facilities').first()
    if public is None:
        return  # Fresh installations use seed_categories.

    tourism, _ = categories.get_or_create(slug='tourism', defaults={
        'name_ar': 'السياحة والترفيه', 'name_en': 'Tourism and Entertainment', 'sort_order': 10, 'icon': 'sparkles',
    })
    culture, _ = categories.get_or_create(slug='culture', defaults={
        'name_ar': 'ثقافة', 'name_en': 'Culture', 'sort_order': 8, 'icon': 'book',
    })
    for slug, name_ar, name_en in [
        ('parks', 'الحدائق', 'Parks'), ('squares', 'الساحات', 'Squares'),
        ('departure-centers', 'مراكز الانطلاق (البولمان)', 'Coach Departure Centers'),
        ('airports', 'مطار دمشق الدولي', 'Damascus International Airport'),
        ('rubble-dumps', 'مكبات الأنقاض', 'Rubble Dumps'),
    ]:
        subs.filter(category=public, slug=slug).update(name_ar=name_ar, name_en=name_en)

    for sub in subs.filter(category=public, slug__in=[
        'historic-landmarks', 'stadiums', 'cinemas', 'theaters', 'malls', 'opera-house',
    ]):
        destination = culture if sub.slug == 'opera-house' else tourism
        existing = subs.filter(category=destination, slug=sub.slug).first()
        if existing:
            places.filter(sub_category=sub).update(sub_category=existing)
            sub.delete()
        else:
            sub.category = destination
            sub.save(using=db, update_fields=['category'])

    removed = subs.filter(category=public, slug__in=['interchange-centers', 'entertainment'])
    places.filter(sub_category__in=removed).delete()
    removed.delete()


class Migration(migrations.Migration):
    dependencies = [('places', '0008_government_subcategories')]
    operations = [migrations.RunPython(reorganize_facilities)]
