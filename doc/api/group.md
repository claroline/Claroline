### `GET` /api/groups.{_format} ###

_Returns the groups list_

#### Requirements ####


**_format**
  - Requirement: json|xml


### `POST` /api/groups.{_format} ###

_Create a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

#### Parameters ####

group_form:

  * type: object (GroupType)
  * required: true

group_form[name]:

  * type: string
  * required: true
  * description: name


### `GET` /api/groups/{group}.{_format} ###

_Returns a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**group**


### `PUT` /api/groups/{group}.{_format} ###

_Update a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**group**

#### Parameters ####

group_form:

  * type: object (GroupType)
  * required: false

group_form[name]:

  * type: string
  * required: true
  * description: name


### `DELETE` /api/groups/{group}.{_format} ###

_Removes a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**group**


### `PATCH` /api/groups/{group}/roles/{role}/add.{_format} ###

_Add a role to a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**group**

**role**


### `GET` /api/groups/{group}/roles/{role}/remove.{_format} ###

_Remove a role from a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**group**

**role**
