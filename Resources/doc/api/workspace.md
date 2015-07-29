### `GET` /api/workspaces.{_format} ###

_Returns the workspaces list_

#### Requirements ####


**_format**
  - Requirement: json|xml


### `POST` /api/workspaces/{user}/users.{_format} ###

_Create a workspace_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

#### Parameters ####

workspace_form:

  * type: object (WorkspaceType)
  * required: true

workspace_form[name]:

  * type: string
  * required: true
  * description: name

workspace_form[code]:

  * type: string
  * required: true
  * description: code

workspace_form[description]:

  * type: string
  * required: false
  * description: description

workspace_form[displayable]:

  * type: boolean
  * required: false
  * description: displayable_in_workspace_list

workspace_form[selfRegistration]:

  * type: boolean
  * required: false
  * description: public_registration

workspace_form[registrationValidation]:

  * type: boolean
  * required: false
  * description: registration_validation

workspace_form[selfUnregistration]:

  * type: boolean
  * required: false
  * description: public_unregistration

workspace_form[maxStorageSize]:

  * type: string
  * required: true
  * description: max_storage_size

workspace_form[maxUploadResources]:

  * type: string
  * required: true
  * description: max_amount_resources

workspace_form[maxUsers]:

  * type: string
  * required: true
  * description: workspace_max_users

workspace_form[endDate]:

  * type: datetime
  * required: true
  * description: expiration_date


### `GET` /api/workspaces/{workspace}.{_format} ###

_Returns a workspace_

#### Requirements ####


**_format**
  - Requirement: json|xml

**workspace**


### `DELETE` /api/workspaces/{workspace}.{_format} ###

_Removes a workspace_

#### Requirements ####


**_format**
  - Requirement: json|xml

**workspace**


### `PUT` /api/workspaces/{workspace}/users/{user}.{_format} ###

_Update a workspace_

#### Requirements ####


**_format**
  - Requirement: json|xml

**workspace**

**user**

#### Parameters ####

workspace_form:

  * type: object (WorkspaceType)
  * required: false

workspace_form[name]:

  * type: string
  * required: true
  * description: name

workspace_form[code]:

  * type: string
  * required: true
  * description: code

workspace_form[description]:

  * type: string
  * required: false
  * description: description

workspace_form[displayable]:

  * type: boolean
  * required: false
  * description: displayable_in_workspace_list

workspace_form[selfRegistration]:

  * type: boolean
  * required: false
  * description: public_registration

workspace_form[registrationValidation]:

  * type: boolean
  * required: false
  * description: registration_validation

workspace_form[selfUnregistration]:

  * type: boolean
  * required: false
  * description: public_unregistration

workspace_form[maxStorageSize]:

  * type: string
  * required: true
  * description: max_storage_size

workspace_form[maxUploadResources]:

  * type: string
  * required: true
  * description: max_amount_resources

workspace_form[maxUsers]:

  * type: string
  * required: true
  * description: workspace_max_users

workspace_form[endDate]:

  * type: datetime
  * required: true
  * description: expiration_date
