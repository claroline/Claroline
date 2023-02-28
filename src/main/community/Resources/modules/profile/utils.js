import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {hasPermission} from '#/main/app/security'

import {formatSections} from '#/main/app/content/form/parameters/utils'

function getMainFacet(facets) {
  return facets.find(facet => facet.meta.main)
}

function getDefaultFacet() {
  return {
    id: 'main',
    title: trans('general'),
    display: {
      order: 0
    },
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
      }, {
        name: 'mainOrganization',
        type: 'organization',
        displayed: hasPermission('administrate', user),
        label: trans('main_organization')
      }
    ]
  }
}

function getFormDefaultSections(user, update, isNew = false) {
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
          displayed: param('community.username')
        }, {
          name: 'plainPassword',
          type: 'password',
          label: trans('password'),
          displayed: isNew,
          required: true
        }, {
          name: 'mainOrganization',
          type: 'organization',
          required: true,
          displayed: hasPermission('administrate', user),
          label: trans('main_organization')
        }
      ]
    }, {
      icon: 'fa fa-fw fa-circle-info',
      title: trans('information'),
      fields: [
        {
          name: 'administrativeCode',
          type: 'string',
          displayed: hasPermission('administrate', user),
          label: trans('administrativeCode')
        }, {
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
        }, {
          name: 'meta.locale',
          type: 'locale',
          label: trans('language'),
          required: true,
          displayed: isNew || hasPermission('administrate', user),
          options: {
            onlyEnabled: true
          }
        }
      ]
    }, {
      icon: 'fa fa-fw fa-key',
      title: trans('access_restrictions'),
      displayed: hasPermission('administrate', user),
      fields: [
        {
          name: 'restrictions.enableDates',
          type: 'boolean',
          label: trans('restrict_by_dates'),
          calculated: (user) => user.restrictions && 0 !== user.restrictions.dates.length,
          onChange: (activated) => {
            if (!activated) {
              update('restrictions.dates', [])
            } else {
              update('restrictions.dates', [null, null])
            }
          },
          linked: [
            {
              name: 'restrictions.dates',
              type: 'date-range',
              label: trans('access_dates'),
              displayed: (user) => user.restrictions && 0!== user.restrictions.dates.length,
              required: true,
              options: {
                time: true
              }
            }
          ]
        }
      ]
    }
  ]
}

function formatFormSections(sections, allFields, userData, params = {}, currentUser = null) {
  const hasConfidentialRights = currentUser ? currentUser.id === userData.id || hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params.roles_confidential)) : false
  const hasLockedRights = currentUser ? hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params.roles_locked)) : false

  return formatSections(sections, allFields, 'profile', hasConfidentialRights, hasLockedRights)
}

function formatDetailsSections(sections, allFields, userData, params, currentUser) {
  const hasConfidentialRights = currentUser ? currentUser.id === userData.id || hasRoles(currentUser.roles, ['ROLE_ADMIN'].concat(params.roles_confidential)) : false

  return formatSections(sections, allFields, 'profile', hasConfidentialRights, true)
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
  formatDetailsSections
}
