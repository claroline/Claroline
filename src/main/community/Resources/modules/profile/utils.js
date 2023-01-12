import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'

function getMainFacet(facets) {
  return facets.find(facet => facet.meta.main)
}

function getDefaultFacet() {
  return {
    id: 'main',
    title: trans('general'),
    position: 0,
    meta: {
      main: true
    },
    sections: []
  }
}

function getDetailsDefaultSection(parameters, user) {
  return {
    id: 'default-props',
    title: trans('general'),
    primary: true,
    fields: [
      {
        name: 'email',
        type: 'email',
        label: trans('email'),
        displayed: !isEmpty(user.email)
      }, {
        name: 'phone',
        type: 'string',
        label: trans('phone'),
        displayed: !isEmpty(user.email)
      }, {
        name: 'meta.description',
        type: 'html',
        label: trans('description'),
        options: {
          minRows: 5
        }
      }
    ]
  }
}

function getFormDefaultSections(user, isNew = false) {
  return [
    {
      id: 'default-props',
      title: trans('general'),
      primary: true,
      fields: [
        {
          name: 'lastName',
          type: 'string',
          label: trans('last_name'),
          required: true
        }, {
          name: 'firstName',
          type: 'string',
          label: trans('first_name'),
          required: true
        }, {
          name: 'email',
          type: 'email',
          label: trans('email'),
          required: true
        }, {
          name: 'username',
          type: 'username',
          label: trans('username'),
          required: true,
          displayed: isNew && param('community.username')
        }, {
          name: 'plainPassword',
          type: 'password',
          label: trans('password'),
          displayed: isNew,
          required: true
        }
      ]
    }, {
      icon: 'fa fa-fw fa-info',
      title: trans('information'),
      fields: [
        {
          name: 'meta.description',
          type: 'html',
          label: trans('description')
        }, {
          name: 'phone',
          type: 'string',
          label: trans('phone')
        }
      ]
    }, {
      icon: 'fa fa-fw fa-desktop',
      title: trans('display_parameters'),
      fields: [
        {
          name: 'picture',
          type: 'image',
          label: trans('picture')
        }, {
          name: 'poster',
          label: trans('poster'),
          type: 'image'
        }, {
          name: 'thumbnail',
          label: trans('thumbnail'),
          type: 'image'
        }
      ]
    }
  ]
}

function formatFormSections(sections, allFields, userData, params = {}, currentUser = null) {
  const hasConfidentialRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params.roles_confidential)): false
  const hasLockedRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params.roles_locked)): false

  return sections.map(section => {
    section.fields = section.fields
      .filter(f => !get(f, 'restrictions.hidden') && (hasConfidentialRights || !get(f, 'restrictions.metadata') || (currentUser && currentUser.id === userData.id)))
      .map(f => {
        if (!hasLockedRights && (
          (f.restrictions.locked && !f.restrictions.lockedEditionOnly) ||
          (f.restrictions.locked && f.restrictions.lockedEditionOnly && null !== get(userData, `profile.${f.id}`, null))
        )) {
          f.disabled = true
        }

        return formatField(f, allFields, 'profile')
      })

    return section
  })
}

function formatDetailsSections(sections, allFields, user, params, currentUser) {
  const hasConfidentialRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params['roles_confidential'])) : false

  return sections.map(section => {
    section.fields = section.fields
      .filter(f => !f.restrictions.hidden && (hasConfidentialRights || !f.restrictions.metadata || (currentUser && currentUser.id === user.id)))
      .map(f => formatField(f, allFields, 'profile'))

    return section
  })
}

function formatField(fieldDef, allFields, dataProp) {
  const field = {
    name: `${dataProp}.${fieldDef.id}`,
    type: fieldDef.type,
    label: fieldDef.label,
    required: fieldDef.required,
    help: fieldDef.help,
    options: fieldDef.options ? cloneDeep(fieldDef.options) : {},
    displayed: (data) => isFieldDisplayed(fieldDef, allFields, data[dataProp])
  }

  if (fieldDef.type === 'choice') {
    field.options.choices = fieldDef.options.choices ?
      fieldDef.options.choices.reduce((acc, choice) => Object.assign(acc, {
        [choice.value]: choice.value
      }), {}) : {}
  }

  return field
}

function isFieldDisplayed(fieldDef, allFields, data) {
  if (!isEmpty(fieldDef.display.condition)) {
    const parentField = allFields.find(f => f.id === fieldDef.display.condition.field)
    if (parentField) {
      const parentValue = get(data, parentField.id)

      let displayed = false
      switch (fieldDef.display.condition.comparator) {
        case 'equal':
          displayed = parentValue === fieldDef.display.condition.value
          break
        case 'different':
          displayed = parentValue !== fieldDef.display.condition.value
          break
        case 'empty':
          displayed = isEmpty(parentValue)
          break
        case 'not_empty':
          displayed = !isEmpty(parentValue)
          break
      }

      return displayed
    }
  }

  return true
}

// should be declared elsewhere
function hasRoles(roles, validRoleNames) {
  const validRoles = roles.filter(r => r.type === 1).filter(r => validRoleNames.indexOf(r.name) > -1)

  return validRoles.length > 0
}

export {
  getDetailsDefaultSection,
  getFormDefaultSections,
  getMainFacet,
  getDefaultFacet,
  formatFormSections,
  formatDetailsSections,
  formatField,
  isFieldDisplayed
}
