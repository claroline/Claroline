import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {EditorPage} from '#/main/app/editor'
import {ContentRights} from '#/main/app/content/components/rights'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const restrictedByDates = (formData) => get(formData, 'resourceNode.restrictions.enableDates') || !isEmpty(get(formData, 'resourceNode.restrictions.dates'))
const restrictedByCode = (formData) => get(formData, 'resourceNode.restrictions.enableCode') || !!get(formData, 'resourceNode.restrictions.code')

const ResourceEditorPermissions = (props) => {
  useEffect(() => {
    props.loadRights(props.resourceNode)
  }, [get(props.resourceNode, 'id')])

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('Gérez les différents droits d\'accès et de modifications de vos utilisateurs.')}
      managerOnly={true}
      definition={[
        {
          name: 'download',
          icon: 'fa fa-fw fa-download',
          title: trans('download'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.downloadable',
              type: 'boolean',
              label: trans('Autoriser le téléchargement'),
              help: trans('Les utilisateurs ayant la permission "Ouvrir" peuvent télécharger une copie locale de la ressource.')
            }
          ]
        }, {
          name: 'roles',
          icon: 'fa fa-fw fa-id-badges',
          title: trans('roles'),
          subtitle: trans('Assignez des permissions aux rôles pour personnaliser les droits des utilisateurs possédant ce rôle.'),
          primary: true,
          render: () => (
            <>
              {props.rights &&
                <ContentRights
                  workspace={props.resourceNode.workspace}
                  creatable={param('resources.types').reduce((resourceTypes, current) => Object.assign(resourceTypes, {
                    [current.name]: trans(current.name, {}, 'resource')
                  }), {})}
                  rights={props.rights}
                  updateRights={props.updateRights}
                />
              }

              {'directory' === get(props.resourceNode, 'meta.type') &&
                <Checkbox
                  className="form-switch form-check-reverse"
                  id={'recursive-node-' + props.resourceNode.id}
                  label={trans('apply_recursively_to_directories', {}, 'platform')}
                  checked={props.recursiveEnabled}
                  onChange={value => props.setRecursiveEnabled(value)}
                  inline={true}
                />
              }
            </>
          )
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          subtitle: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.restrictions.enableDates',
              label: trans('restrict_by_dates'),
              type: 'boolean',
              calculated: restrictedByDates,
              onChange: activated => {
                if (!activated) {
                  props.updateProp('restrictions.dates', [])
                }
              },
              linked: [
                {
                  name: 'resourceNode.restrictions.dates',
                  type: 'date-range',
                  label: trans('access_dates'),
                  displayed: restrictedByDates,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'resourceNode.restrictions.enableCode',
              label: trans('restrict_by_code'),
              type: 'boolean',
              calculated: restrictedByCode,
              onChange: activated => {
                if (!activated) {
                  props.updateProp('restrictions.code', '')
                }
              },
              linked: [
                {
                  name: 'resourceNode.restrictions.code',
                  label: trans('access_code'),
                  displayed: restrictedByCode,
                  type: 'password',
                  required: true
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

ResourceEditorPermissions.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  updateRights: T.func.isRequired,
  loadRights: T.func.isRequired,

  setRecursiveEnabled: T.func.isRequired,
  recursiveEnabled: T.bool
}

ResourceEditorPermissions.defaultProps = {
  recursiveEnabled: false
}

export {
  ResourceEditorPermissions
}
