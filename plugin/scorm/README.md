# ScormBundle

## Export a Claroline resource into a SCORM package

The ScormBundle allows to create a SCORM package based on a Claroline resource through a Symfony command : 

```
$ php app/console claroline:scorm:export RESOURCE_NODE_ID --scormversion=2004|1.2 --locale=en|fr|es
```

The command takes the ID of the ResourceNode as the only required argument. 

It is possible to specify the SCORM version with the option `--scorm-version (-sc)`. By default, the exporter uses SCORM 2004.
It is also possible to choose the locale for export through `--locale (-l)`. By default, the exporter uses the English.

## Implement SCORM export for a Claroline resource

### Listening to the export event

The exporter dispatches a `ExportScormResourceEvent` event. So in order to export a Resource, you need to listen to the emitted event.
The event name is `export_scorm_[RESOURCE_TYPE]`.

The `ExportScormResourceEvent` class contains the Resource to export and the locale used.

### Populating the export event

The only required property is the template of the Resource (e.g. the result of a compiled Twig).
It can be set with `ExportScormResourceEvent::setTemplate(string $template)`.

It's also possible to :
- Add Resource assets (css, js, images, etc.)
- Register translation domains
- Add embed Resources (the exporter will dispatch the `ExportScormResourceEvent` for each one)

(See `ExportScormResourceEvent` class for more information)

### Using common files

The exporter automatically adds a bunch of common Claroline assets in order to simplify the export of the Resources.

**NB :** As all resources do not require all the Claroline assets, you need to include them manually into your template

#### JavaScripts

JS files are added to a `commons` directory.

- `jquery.min.js`
- `jquery-ui.min.js`
- `router.js` - js router
- `routes.js` - exposed JS routes
- `translator.js` - js translator

#### Stylesheets

CSS files are added to a `commons` directory.

- `bootstrap.css` - Bootstrap + Claroline default theme
- `font-awesome.css`

#### Translation domains

Translation files are added to a `translations` directory. The following domains are always included in the export :

- resource
- home
- platform
- error
- validators

### Example
