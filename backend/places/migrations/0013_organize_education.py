from django.db import migrations


def organize_education(apps, schema_editor):
    db = schema_editor.connection.alias
    Category = apps.get_model('places', 'Category')
    SubCategory = apps.get_model('places', 'SubCategory')
    education = Category.objects.using(db).filter(slug='education').first()
    if not education:
        return

    university, _ = SubCategory.objects.using(db).update_or_create(
        category=education, slug='damascus-university',
        defaults={'name_ar': 'جامعة دمشق', 'name_en': 'Damascus University'},
    )
    children = (
        ('colleges', 'كليات', 'Colleges'),
        ('university-institutes', 'معاهد', 'Institutes'),
        ('security-sciences-college', 'كلية العلوم الأمنية', 'College of Security Sciences'),
        ('defense-sciences-college', 'كلية العلوم الدفاعية', 'College of Defense Sciences'),
        ('virtual-university-access-centers', 'مراكز نفاذ الجامعة الافتراضية', 'Virtual University Access Centers'),
    )
    for slug, name_ar, name_en in children:
        SubCategory.objects.using(db).update_or_create(
            category=education, slug=slug,
            defaults={'name_ar': name_ar, 'name_en': name_en, 'parent': university},
        )
    SubCategory.objects.using(db).filter(category=education, slug='institutes').update(
        name_ar='معاهد الشؤون الاجتماعية', name_en='Social Affairs Institutes', parent=None,
    )



class Migration(migrations.Migration):
    dependencies = [('places', '0012_university_subcategories')]
    operations = [migrations.RunPython(organize_education, migrations.RunPython.noop)]
