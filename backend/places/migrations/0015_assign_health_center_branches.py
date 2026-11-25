from django.db import migrations


def assign_health_centers(apps, schema_editor):
    db = schema_editor.connection.alias
    subs = apps.get_model('places', 'SubCategory').objects.using(db)
    places = apps.get_model('places', 'Place').objects.using(db).filter(
        sub_category__category__slug='healthcare', sub_category__slug='health-centers',
    )
    for sector, slug in (
        ('public', 'public-health-centers'), ('private', 'private-health-centers'),
    ):
        branch = subs.get(category__slug='healthcare', slug=slug)
        places.filter(sector__iexact=sector).update(sub_category=branch)


class Migration(migrations.Migration):
    dependencies = [('places', '0014_health_center_subcategories')]
    operations = [migrations.RunPython(assign_health_centers, migrations.RunPython.noop)]
