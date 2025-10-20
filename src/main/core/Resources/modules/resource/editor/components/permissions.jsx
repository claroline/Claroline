import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {EditorPage} from '#/main/app/editor'
import {ContentRights} from '#/main/app/content/components/rights'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {supportDownload, getResource} from '#/main/core/resource/utils'

const restrictedByDates = (formData) => get(formData, 'resourceNode.restrictions.enableDates') || !isEmpty(get(formData, 'resourceNode.restrictions.dates'))
const restrictedByCode = (formData) => get(formData, 'resourceNode.restrictions.enableCode') || !!get(formData, 'resourceNode.restrictions.code')

const ResourceEditorPermissions = (props) => {
  const [permissions, setPermissions] = useState({})

  useEffect(() => {
    if (!isEmpty(props.resourceNode)) {
      // load tool configuration to get the list of implemented permissions
      getResource(get(props.resourceNode, 'meta.type')).then((resourceModule) => {
        setPermissions(resourceModule.default.permissions)
      })

      props.loadRights(props.resourceNode)
    }
  }, [get(props.resourceNode, 'id')])

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('permissions_help')}
      managerOnly={true}
      definition={[
        {
          name: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.meta.public',
              type: 'boolean',
              label: trans('make_resource_public', {}, 'resource'),
              help: [
                trans('make_resource_public_help', {}, 'resource'),
                trans('make_resource_public_warning', {}, 'resource')
              ]
            }, {
              name: 'resourceNode.meta.downloadable',
              type: 'boolean',
              displayed: !isEmpty(props.resourceNode) && supportDownload(props.resourceNode),
              label: trans('allow_download', {}, 'resource'),
              help: trans('allow_download_help', {}, 'resource')
            }
          ]
        }, {
          name: 'roles',
          title: trans('roles'),
          description: trans('Assignez des permissions aux rôles pour personnaliser les droits des utilisateurs possédant ce rôle.'),
          primary: true,
          render: () => props.rights && (
            <ContentRights
              workspace={props.resourceNode.workspace}
              permissions={permissions}
              creatable={param('resources.types').reduce((resourceTypes, current) => Object.assign(resourceTypes, {
                [current.name]: trans(current.name, {}, 'resource')
              }), {})}
              rights={props.rights}
              updateRights={props.updateRights}
            />
          )
        }, {
          name: 'restrictions',
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          description: trans('Ajoutez des conditions d\'accès supplémentaires à vos contenus. Les utilisateurs ayant la permission "Administrer" ne sont pas affectés.'),
          primary: true,
          fields: [
            {
              name: 'resourceNode.restrictions.enableDates',
              label: trans('restrict_by_dates'),
              help: trans('restrict_by_dates_help'),
              type: 'boolean',
              calculated: restrictedByDates,
              onChange: activated => {
                if (!activated) {
                  props.updateResourceNode('restrictions.dates', [])
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
              help: trans('restrict_by_code_help'),
              type: 'boolean',
              calculated: restrictedByCode,
              onChange: activated => {
                if (!activated) {
                  props.updateResourceNode('restrictions.code', '')
                }
              },
              linked: [
                {
                  name: 'resourceNode.restrictions.code',
                  label: trans('access_code'),
                  displayed: restrictedByCode,
                  type: 'password',
                  required: true,
                  options: {
                    disablePasswordCheck: true
                  }
                }
              ]
            }
          ]
        }
      ].concat(props.definition || [])}
    />
  )
}

ResourceEditorPermissions.propTypes = {
  definition: T.array,
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  updateResourceNode: T.func.isRequired,
  updateRights: T.func.isRequired,
  loadRights: T.func.isRequired
}

export {
  ResourceEditorPermissions
}
