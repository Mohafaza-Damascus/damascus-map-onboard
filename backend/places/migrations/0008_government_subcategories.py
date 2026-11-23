from django.db import migrations


def update_government_subcategories(apps, schema_editor):
    db = schema_editor.connection.alias
    government = apps.get_model('places', 'Category').objects.using(db).filter(slug='government').first()
    if government is None:
        return  # On a fresh database seed_categories creates the updated categories.

    subs = apps.get_model('places', 'SubCategory').objects.using(db)
    subs.filter(category=government, slug='police-stations').update(
        name_ar='مراكز الأمن العام', name_en='Public Security Centers',
    )
    passports = subs.filter(category__slug='public-facilities', slug='immigration-passports').first()
    if passports:
        target = subs.filter(category=government, slug=passports.slug).first()
        if target:
            apps.get_model('places', 'Place').objects.using(db).filter(sub_category=passports).update(sub_category=target)
            passports.delete()
        else:
            passports.category = government
            passports.save(using=db, update_fields=['category'])

    subs.get_or_create(category=government, slug='civil-affairs',
                       defaults={'name_ar': 'الشؤون المدنية', 'name_en': 'Civil Affairs'})


class Migration(migrations.Migration):
    dependencies = [('places', '0007_governorate_subcategories')]
    operations = [migrations.RunPython(update_government_subcategories)]
