from django.db import migrations


def unclassify_mezzeh_airport(apps, schema_editor):
    apps.get_model('places', 'Place').objects.using(schema_editor.connection.alias).filter(
        title_ar='مطار المزة',
        sub_category__slug='airports',
        sub_category__category__slug='public-facilities',
    ).update(sub_category=None)


class Migration(migrations.Migration):
    dependencies = [('places', '0009_public_facilities_and_tourism')]
    operations = [migrations.RunPython(unclassify_mezzeh_airport)]
