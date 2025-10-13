import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {param} from '#/main/app/config'
import {EditorPage} from '#/main/app/editor'
import {ContentRights} from '#/main/app/content/components/rights'

import {supportDownload, getResource} from '#/main/core/resource/utils'
import {actions, selectors} from '#/main/core/resource/editor/store'

const restrictedByDates = (formData) => get(formData, 'resourceNode.restrictions.enableDates') || !isEmpty(get(formData, 'resourceNode.restrictions.dates'))
const restrictedByCode = (formData) => get(formData, 'resourceNode.restrictions.enableCode') || !!get(formData, 'resourceNode.restrictions.code')

const ResourceEditorPermissions = (props) => {
  const dispatch = useDispatch()
  const [permissions, setPermissions] = useState({})

  const resourceNode = useSelector(selectors.resourceNode)
  const rights = useSelector(selectors.rights)

  useEffect(() => {
    if (!isEmpty(resourceNode)) {
      // load resource configuration to get the list of implemented permissions
      getResource(get(resourceNode, 'meta.type')).then((resourceModule) => {
        setPermissions(resourceModule.default.permissions)
      })

      dispatch(actions.fetchRights(resourceNode))
    }
  }, [get(resourceNode, 'id')])

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
              displayed: !isEmpty(resourceNode) && supportDownload(resourceNode),
              label: trans('allow_download', {}, 'resource'),
              help: trans('allow_download_help', {}, 'resource')
            }
          ]
        }, {
          name: 'roles',
          title: trans('roles'),
          description: trans('Assignez des permissions aux rôles pour personnaliser les droits des utilisateurs possédant ce rôle.'),
          primary: true,
          render: () => rights && (
            <ContentRights
              workspace={resourceNode.workspace}
              permissions={permissions}
              creatable={param('resources.types').reduce((resourceTypes, current) => Object.assign(resourceTypes, {
                [current.name]: trans(current.name, {}, 'resource')
              }), {})}
              rights={rights}
              updateRights={(perms) => dispatch(actions.updateRights(perms))}
            />
          )
        }, {
          name: 'restrictions',
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
                  dispatch(actions.updateResourceNode('restrictions.dates', []))
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
                  dispatch(actions.updateResourceNode('restrictions.code', ''))
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
  definition: T.array
}

export {
  ResourceEditorPermissions
}
