import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

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
  let displayEmail = false

  const showEmailRoles = get(parameters, 'show_email', []) || []
  showEmailRoles.forEach(role => {
    user.roles.forEach(userRole => {
      if (userRole.name === role) {
        displayEmail = true
      }
    })
  })

  return {
    id: 'default-props',
    title: trans('general'),
    primary: true,
    fields: [
      {
        name: 'email',
        type: 'email',
        label: trans('email'),
        displayed: displayEmail
      }, {
        name: 'phone',
        type: 'string',
        label: trans('phone'),
        displayed: displayEmail
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

function getFormDefaultSections(userData, isNew = false) {
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
          name: 'phone',
          type: 'string',
          label: trans('phone')
        }, {
          name: 'username',
          type: 'username',
          label: trans('username'),
          required: true,
          disabled: !isNew && (!userData.meta || !userData.meta.administrate)
        }, {
          name: 'plainPassword',
          type: 'password',
          label: trans('password'),
          displayed: isNew,
          required: true
        }, {
          name: 'meta.description',
          type: 'html',
          label: trans('description'),
          options: {
            minRows: 5
          }
        }, {
          name: 'meta.locale',
          type: 'locale',
          label: trans('language'),
          required: true,
          options: {
            onlyEnabled: true
          }
        }, {
          name: 'picture',
          type: 'image',
          label: trans('picture')
        }
      ]
    }, {
      icon: 'fa fa-fw fa-desktop',
      title: trans('display_parameters'),
      fields: [
        {
          name: 'poster',
          label: trans('poster'),
          type: 'image',
          options: {
            ratio: '3:1'
          }
        }, {
          name: 'thumbnail',
          label: trans('thumbnail'),
          type: 'image'
        }
      ]
    }
  ]
}

//the `force` param is here for the user registration: just show everything
function formatFormSections(sections, userData, params, currentUser = null) {
  let hasConfidentialRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params['roles_confidential'])): false
  let hasLockedRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params['roles_locked'])): false

  sections.forEach(section => {
    section.fields = section.fields.filter(f => !f.restrictions.hidden && (hasConfidentialRights || !f.restrictions.metadata || (currentUser && currentUser.id === userData['id'])))
    section.fields.forEach(f => {
      f['name'] = 'profile.' + f['id']

      if (!hasLockedRights && (
        (f.restrictions.locked && !f.restrictions.lockedEditionOnly) ||
        (f.restrictions.locked && f.restrictions.lockedEditionOnly && userData['profile'][f.id] !== undefined && userData['profile'][f.id] !== null)
      )) {
        f['disabled'] = true
      }
      if (f.type === 'choice') {
        const options = f.options ? f.options : {}
        options['choices'] = f.options.choices ?
          f.options.choices.reduce((acc, choice) => {
            acc[choice.value] = choice.value

            return acc
          }, {}) :
          {}
        f['options'] = options
      }
    })
  })

  return sections
}

function formatDetailsSections(sections, user, params, currentUser) {
  const hasConfidentialRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params['roles_confidential'])) : false
  sections.forEach(section => {
    section.fields = section.fields.filter(f => !f.restrictions.hidden && (hasConfidentialRights || !f.restrictions.metadata || (currentUser && currentUser.id === user.id)))
    section.fields.forEach(f => {
      f['name'] = 'profile.' + f['id']

      if (f.type === 'choice') {
        const options = f.options ? f.options : {}
        options['choices'] = f.options.choices ?
          f.options.choices.reduce((acc, choice) => {
            acc[choice.value] = choice.value

            return acc
          }, {}) :
          {}
        f['options'] = options
      }
    })
  })

  return sections
}

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
  formatDetailsSections
}
