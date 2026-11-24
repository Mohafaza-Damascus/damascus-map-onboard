from django.db import migrations, models
import django.db.models.deletion


class Migration(migrations.Migration):
    dependencies = [('places', '0011_expand_place_map_bounds')]
    operations = [
        migrations.AddField(
            model_name='subcategory', name='parent',
            field=models.ForeignKey(on_delete=django.db.models.deletion.CASCADE,
                                    null=True, blank=True, related_name='children', to='places.subcategory'),
        ),
    ]
