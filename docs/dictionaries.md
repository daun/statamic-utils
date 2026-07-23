# Dictionaries

[Dictionaries](https://statamic.dev/dictionaries) provide dynamic option lists for `dictionary`
fieldtypes in your blueprints.

- `Collections`: List all collections, keyed by handle with their title as label.
- `Locales`: List all sites, keyed by short locale with the site name as label.

```yaml
# In a blueprint field
field:
  type: dictionary
  dictionary:
    type: \Daun\StatamicUtils\Dictionaries\Collections
```
