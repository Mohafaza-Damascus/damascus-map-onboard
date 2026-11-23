from django.db import migrations


def merge_municipalities(apps, schema_editor):
    db = schema_editor.connection.alias
    category = apps.get_model('places', 'Category').objects.using(db).filter(
        slug='damascus-governorate'
    ).first()
    if category is None:
        return  # New installations get the current taxonomy from seed_categories.

    subs = apps.get_model('places', 'SubCategory').objects.using(db)
    services, _ = subs.update_or_create(
        category=category, slug='municipality-buildings',
        defaults={'name_ar': 'دوائر الخدمات (البلديات)', 'name_en': 'Service Departments (Municipalities)'},
    )
    old = subs.filter(category=category, slug='municipalities')
    apps.get_model('places', 'Place').objects.using(db).filter(sub_category__in=old).update(sub_category=services)
    old.delete()
    subs.update_or_create(
        category=category, slug='governorate-directorates',
        defaults={'name_ar': 'المديريات', 'name_en': 'Directorates'},
    )


class Migration(migrations.Migration):
    dependencies = [('places', '0006_routing_geography_index')]
    operations = [migrations.RunPython(merge_municipalities)]
