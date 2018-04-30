import {trans} from '#/main/core/translation'
import {currentUser} from '#/main/core/user/current'

const authenticatedUser = currentUser()

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
    sections: [

    ]
  }
}

function getDetailsDefaultSection() {
  return {
    id: 'default-props',
    title: trans('general'),
    primary: true,
    fields: [
      {
        name: 'email',
        type: 'email',
        label: trans('email')
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

function getFormDefaultSection(userData, isNew = false) {
  return {
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
  }
}

function formatFormSections(sections, userData, params) {
  const hasConfidentialRights = hasRoles(authenticatedUser.roles, ['ROLE_ADMIN'].concat(params['roles_confidential']))
  const hasLockedRights = hasRoles(authenticatedUser.roles, ['ROLE_ADMIN'].concat(params['roles_locked']))

  sections.forEach(section => {
    section.fields = section.fields.filter(f => !f.restrictions.hidden && (hasConfidentialRights || !f.restrictions.isMetadata || (authenticatedUser && authenticatedUser.id === userData['id'])))
    section.fields.forEach(f => {
      f['name'] = f['id']

      if (!hasLockedRights && (
        (f.restrictions.locked && !f.restrictions.lockedEditionOnly) ||
        (f.restrictions.locked && f.restrictions.lockedEditionOnly && userData[f.id] !== undefined && userData[f.id] !== null)
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

function formatDetailsSections(sections, user, params) {
  const hasConfidentialRights = hasRoles(authenticatedUser.roles, ['ROLE_ADMIN'].concat(params['roles_confidential']))
  sections.forEach(section => {
    section.fields = section.fields.filter(f => !f.restrictions.hidden && (hasConfidentialRights || !f.restrictions.isMetadata || (authenticatedUser && authenticatedUser.id === user.id)))
    section.fields.forEach(f => {
      f['name'] = f['id']

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
  getFormDefaultSection,
  getMainFacet,
  getDefaultFacet,
  formatFormSections,
  formatDetailsSections
}
