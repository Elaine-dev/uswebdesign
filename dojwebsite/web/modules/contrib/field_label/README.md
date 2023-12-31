## Field Label
Field Label extends all field formatters to allow authorized users to:
- Change field label text values
- Choose an HTML tag from a configurable list to use as the label wrapper
- Add one or more classes to the label tag

## Configuration
Each of the above features can be enabled/disabled according to the needs of
the project. Additionally Field Label provides a permission for each feature to
allow granular control of who (and how) can edit labels.

Field formatter permissions can be particularly useful when Layout Builder is
in use, and layout overrides potentially expose field formatters to users who
have never had access to settings that were only available to administrators or
site builders in the past (e.g. manage display).

## Requirements
- Drupal core 8.7.6 or higher
*Field Label requires 
[#3029627: FormatterBase should pass along thire party settings](https://www.drupal.org/project/drupal/issues/3029627), which was added to core with the 8.7.6 patch release.*
