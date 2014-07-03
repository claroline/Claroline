Upgrade from 2.x to 3.0
=======================

Resource manager
----------------

The manager has now separated methods for full and picker modes initialization:

    // creates a full manager with its associated default pickers
    Claroline.Resource.Manager.createFullManager(parameters);

    // creates a custom resource picker
    Claroline.Resource.Manager.createPicker(name, parameters);

Several pickers may be created with the second method. Each of them has its own
configuration, so you can create different pickers with different settings on
the same page:

    Claroline.Resource.Manager.createPicker('pickerA', { isMultiSelectAllowed: true });
    Claroline.Resource.Manager.createPicker('pickerB', { isMultiSelectAllowed: false });

Manager parameters are globally the same than in the previous version, but some of them
have been removed or renamed, so have on eye on the documentation of the two methods.

Once initialized, pickers can be opened and closed using the `picker` method:

    Claroline.Resource.Manager.picker('pickerA'); // defaults to "open"
    Claroline.Resource.Manager.picker('pickerB', 'open');
    Claroline.Resource.Manager.picker('pickerA', 'close');

Additionally, some new features have been introduced :

- The content of the initial directory can now be loaded on server side using the
  "preFetchedDirectory" parameter.

- In picker mode, most of the parameters can be omitted. If some important parameter is missing,
  it will be fetched automatically from the server. As a result, picker initialization is almost
  a one-liner: the only relevant parameters remain the callback function and a few options related
  to the picker behaviour.

- Pickers now accept a white list or a black list of resource types through the *typeWhiteList* and
  *typeBlackList* parameters, allowing to filter the resources to display.

- In picker mode, the current directory id is saved in session and shared amongst
  all picker instances.

- A "resourcePicker" form field has been added (currently supporting only "data-blacklist"
  attributes).


Workspace entity
----------------

The entities `AbstractWorkspace`, `SimpleWorkspace` and `AggregatorWorkspace` have been replaced by
a single `Workspace` entity. The interface of this new class is the same than the previous `SimpleWorkspace`
class. However, don't forget to update your DQL queries and your method signatures if needed.


Font Awesome
------------

Icon classes must be adapted to the follow the convention used in the last version of font awesome.

Before:

    <icon class="icon-plus"/>

After:

    <icon class="fa fa-plus"/>
