# Style Options Drupal Module

Style Options provides configurable style options (CSS classes, background colors, background images, etc.)
for various plugins (Layout Plugins, Paragraph Behaviors, etc.). Options are defined in modules or themes
in simple YAML config files. See "example.style_options.yml" for example configurations.

## IF MIGRATING FROM "OPTION PLUGIN" (https://drupal.org/project/option_plugin)

1. BACKUP YOUR SITE
2. Install Style Options.
3. Rename your yml configuration files to "[module or theme].style_options.yml".
4. Visit admin/config/style-options/migrate and press the button.