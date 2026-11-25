from django.db import migrations


def organize_healthcare(apps, schema_editor):
    subs = apps.get_model('places', 'SubCategory').objects.using(schema_editor.connection.alias)
    healthcare = apps.get_model('places', 'Category').objects.using(
        schema_editor.connection.alias).filter(slug='healthcare').first()
    if not healthcare:
        return
    for slug, name in (
        ('hospitals', 'المستشفيات'), ('blood-banks', 'بنوك الدم'),
        ('health-centers', 'المراكز الصحية'), ('pharmacies', 'الصيدليات'),
    ):
        subs.filter(category=healthcare, slug=slug).update(name_ar=name)
    parent, _ = subs.get_or_create(category=healthcare, slug='health-centers', defaults={
        'name_ar': 'المراكز الصحية', 'name_en': 'Health Centers',
    })
    for slug, name_ar, name_en in (
        ('dispensaries', 'مستوصفات', 'Dispensaries'),
        ('private-health-centers', 'مراكز صحية خاصة', 'Private Health Centers'),
        ('public-health-centers', 'مراكز صحية عامة', 'Public Health Centers'),
        ('medical-laboratories', 'مخابر التحليل', 'Medical Laboratories'),
        ('radiology-centers', 'مراكز الاشعة', 'Radiology Centers'),
    ):
        subs.update_or_create(category=healthcare, slug=slug, defaults={
            'name_ar': name_ar, 'name_en': name_en, 'parent': parent,
        })


class Migration(migrations.Migration):
    dependencies = [('places', '0013_organize_education')]
    operations = [migrations.RunPython(organize_healthcare, migrations.RunPython.noop)]
