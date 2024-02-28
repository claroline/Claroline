import React from 'react'
import { useHistory } from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const CourseParameters = (props) => {
  const history = useHistory()

  return (
    <FormData
      className="mt-3"
      name={props.name}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        callback: () => props.save(props.course, props.isNew, props.name).then(course => {
          if (props.isNew) {
            history.push(route(course))
          }
        })
      }}
      cancel={{
        type: LINK_BUTTON,
        target: props.isNew ? props.path : route(props.course),
        exact: true
      }}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
              name: 'parent',
              type: 'training_course',
              label: trans('parent')
            }, {
              name: 'description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'plainDescription',
              type: 'string',
              label: trans('plain_description'),
              options: {long: true},
              help: trans('plain_description_help')
            }, {
              name: 'meta.duration',
              type: 'number',
              label: trans('duration'),
              required: true,
              options: {
                min: 0,
                unit: trans('hours')
              }
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }, {
              name: 'organizations',
              type: 'organizations',
              label: trans('organizations')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'poster',
              type: 'image',
              label: trans('poster')
            }, {
              name: 'thumbnail',
              type: 'image',
              label: trans('thumbnail')
            }, {
              name: 'display.order',
              type: 'number',
              label: trans('order'),
              required: true,
              options: {
                min: 0
              }
            }, {
              name: 'display.hideSessions',
              type: 'boolean',
              label: trans('hide_sessions', {}, 'cursus')
            }, {
              name: 'restrictions.hidden',
              type: 'boolean',
              label: trans('restrict_hidden'),
              help: trans('restrict_hidden_help')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-sign-in',
          title: trans('opening_parameters'),
          fields: [
            {
              name: 'opening.session',
              label: trans('opening_session', {}, 'cursus'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: {
                  none: trans('opening_session_none', {}, 'cursus'),
                  first_available: trans('opening_session_first_available', {}, 'cursus'),
                  default: trans('opening_session_default', {}, 'cursus')
                }
              },
              help: trans('opening_session_help', {}, 'cursus')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-book',
          title: trans('workspace'),
          fields: [
            {
              name: '_workspaceType',
              type: 'choice',
              label: trans('type'),
              hideLabel: true,
              options: {
                condensed: false,
                choices: {
                  none: trans('none'),
                  workspace: trans('Utiliser le même espace d\'activités pour toutes les sessions de la formation', {}, 'cursus'),
                  model: trans('Utiliser un modèle d\'espace d\'activités pour générer un nouvel espace pour chaque session de la formation', {}, 'cursus')
                }
              },
              calculated: (course) => {
                if (get(course, '_workspaceType')) {
                  return get(course, '_workspaceType')
                }

                if (get(course, 'workspace', null)) {
                  if (get(props.course, 'workspace.meta.model', false)) {
                    return 'model'
                  }

                  return 'workspace'
                }

                return 'none'
              },
              onChange: () => {
                props.update(props.name, 'workspace', null)
                props.update(props.name, 'registration.tutorRole', null)
                props.update(props.name, 'registration.learnerRole', null)
              },
              linked: [
                {
                  name: 'workspace',
                  type: 'workspace',
                  label: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType') ? trans('workspace_model') : trans('workspace'),
                  required: true,
                  options: {
                    picker: {
                      model: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType'),
                      title: get(props.course, 'workspace.meta.model', false) || 'model' === get(props.course, '_workspaceType') ? trans('workspace_models', {}, 'workspace') : trans('workspaces')
                    }
                  },
                  displayed: (course) => get(course, 'workspace', null) || ['workspace', 'model'].includes(get(course, '_workspaceType'))
                }
              ]
            }, {
              name: 'registration.tutorRole',
              type: 'role',
              label: trans('tutor_role', {}, 'cursus'),
              displayed: (course) => get(course, 'workspace', null),
              options: {
                picker: {
                  url: ['apiv2_workspace_list_roles', {id: get(props.course, 'workspace.id', null)}],
                  filters: []
                }
              },
              help: trans('tutor_role_help', {}, 'cursus')
            }, {
              name: 'registration.learnerRole',
              type: 'role',
              label: trans('learner_role', {}, 'cursus'),
              displayed: (course) => get(course, 'workspace', null),
              options: {
                picker: {
                  url: ['apiv2_workspace_list_roles', {id: get(props.course, 'workspace.id', null)}],
                  filters: []
                }
              },
              help: trans('learner_role_help', {}, 'cursus')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-credit-card',
          title: trans('pricing'),
          displayed: param('pricing.enabled'),
          fields: [
            {
              name: 'pricing.price',
              label: trans('price'),
              type: 'currency',
              linked: [
                {
                  name: 'pricing.description',
                  label: trans('comment'),
                  type: 'string',
                  options: {
                    long: true
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            {
              name: 'restrictions._restrictUsers',
              type: 'boolean',
              label: trans('restrict_users_count'),
              calculated: (course) => !!get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
              onChange: (value) => {
                if (!value) {
                  props.update(props.name, 'restrictions.users', null)
                }
              },
              linked: [
                {
                  name: 'restrictions.users',
                  type: 'number',
                  label: trans('users_count'),
                  required: true,
                  displayed: (course) => get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

CourseParameters.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  save: T.func.isRequired,

  // from store
  isNew: T.bool.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  update: T.func.isRequired
}

export {
  CourseParameters
}
