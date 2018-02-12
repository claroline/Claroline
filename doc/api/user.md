### `GET` /api/users.{_format} ###

_Returns the users list_

#### Requirements ####


**_format**
  - Requirement: json|xml


### `POST` /api/users.{_format} ###

_Creates a user_

#### Requirements ####


**_format**
  - Requirement: json|xml

#### Parameters ####

profile_form_creation:

  * type: object (ProfileCreationType)
  * required: true

profile_form_creation[firstName]:

  * type: string
  * required: true
  * description: first_name

profile_form_creation[lastName]:

  * type: string
  * required: true
  * description: last_name

profile_form_creation[username]:

  * type: string
  * required: true
  * description: username

profile_form_creation[plainPassword]:

  * type: object (RepeatedType)
  * required: true

profile_form_creation[plainPassword][first]:

  * type: string
  * required: true
  * description: password

profile_form_creation[plainPassword][second]:

  * type: string
  * required: true
  * description: verification

profile_form_creation[administrativeCode]:

  * type: string
  * required: false
  * description: administrative_code

profile_form_creation[email]:

  * type: string
  * required: true
  * description: email

profile_form_creation[phone]:

  * type: string
  * required: false
  * description: phone

profile_form_creation[locale]:

  * type: choice
  * required: false
  * description: language

profile_form_creation[authentication]:

  * type: choice
  * required: false
  * description: authentication

profile_form_creation[platformRoles][]:

  * type: array of choices
  * required: true
  * description: roles


### `GET` /api/users/{user}.{_format} ###

_Returns a user_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**


### `PUT` /api/users/{user}.{_format} ###

_Update a user_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

#### Parameters ####

profile_form:

  * type: object (ProfileType)
  * required: false

profile_form[firstName]:

  * type: string
  * required: true
  * description: first_name

profile_form[lastName]:

  * type: string
  * required: true
  * description: last_name

profile_form[username]:

  * type: string
  * required: true
  * description: username

profile_form[administrativeCode]:

  * type: string
  * required: false
  * description: administrative_code

profile_form[email]:

  * type: string
  * required: false
  * description: email

profile_form[phone]:

  * type: string
  * required: false
  * description: phone

profile_form[locale]:

  * type: choice
  * required: false
  * description: language

profile_form[pictureFile]:

  * type: file
  * required: false
  * description: picture_profile

profile_form[description]:

  * type: string
  * required: false
  * description: description

profile_form[authentication]:

  * type: choice
  * required: false
  * description: authentication

profile_form[platformRoles][]:

  * type: array of choices
  * required: true
  * description: roles


### `PATCH` /api/users/{user}/groups/{group}/add.{_format} ###

_Add a user in a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

**group**


### `GET` /api/users/{user}/groups/{group}/remove.{_format} ###

_Remove a user from a group_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

**group**


### `PATCH` /api/users/{user}/roles/{role}/add.{_format} ###

_Add a role to a user_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

**role**


### `GET` /api/users/{user}/roles/{role}/remove.{_format} ###

_remove a role from a user_

#### Requirements ####


**_format**
  - Requirement: json|xml

**user**

**role**
