# ScormBundle

## Export a Claroline resource into a SCORM package

The ScormBundle allows to create a SCORM package based on a Claroline resource through a Symfony command : 

```
$ php app/console claroline:scorm:export RESOURCE_NODE_ID --scormversion=2004|1.2
```

The command takes the ID of the ResourceNode as the only required argument. It is possible to specify the SCORM version 
with the option `--scorm-version (-sc)`. By default, the exporter uses SCORM 2004.

## Implement SCORM export for a Claroline resource

