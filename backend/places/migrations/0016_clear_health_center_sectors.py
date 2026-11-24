from django.db import migrations


def clear_health_center_sectors(apps, schema_editor):
    apps.get_model('places', 'Place').objects.using(schema_editor.connection.alias).filter(
        sub_category__category__slug='healthcare',
        sub_category__slug__in=['public-health-centers', 'private-health-centers'],
    ).update(sector='')


class Migration(migrations.Migration):
    dependencies = [('places', '0015_assign_health_center_branches')]
    operations = [migrations.RunPython(clear_health_center_sectors, migrations.RunPython.noop)]
