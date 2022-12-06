import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import uniq from 'lodash/uniq'
import get from 'lodash/get'
import omit from 'lodash/omit'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentSection} from '#/main/app/content/components/sections'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {MODAL_ROLE_RIGHTS} from '#/main/community/tools/community/role/modals/rights'

const RightsTable = (props) => {
  const allPerms = uniq(Object.keys(props.rights)
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(props.rights[current])
    ), []))

  return (
    <table className="table table-striped table-hover content-rights-advanced">
      <thead>
        <tr>
          <th scope="col">{trans('tool')}</th>

          {allPerms.map(permission =>
            <th key={`${permission}-header`} scope="col">
              <div className="permission-name-container">
                <span className="permission-name">{trans(permission, {}, 'actions')}</span>
              </div>
            </th>
          )}
        </tr>
      </thead>

      <tbody>
        {Object.keys(props.rights).map((toolName) => (
          <tr key={toolName}>
            <th scope="row">{trans(toolName, {}, 'tools')}</th>
            {allPerms.map(toolPerm => (
              <td
                key={toolPerm}
                className="checkbox-cell"
              >
                {!isUndefined(props.rights[toolName][toolPerm]) &&
                  <input
                    type="checkbox"
                    checked={get(props.rights, `${toolName}.${toolPerm}`, false)}
                    disabled={true}
                  />
                }
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  )
}

RightsTable.propsTypes = {
  rights: T.object
}

const RoleRights = (props) =>
  <ContentSection
    {...omit(props, 'contextType', 'contextId', 'role', 'rights', 'reload')}
    id={props.id}
    icon={props.icon}
    title={props.title}
    disabled={props.disabled || isEmpty(props.rights)}
    actions={[
      {
        name: 'set-rights',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        displayed: hasPermission('administrate', props.role),
        modal: [MODAL_ROLE_RIGHTS, {
          title: props.title,
          role: props.role,
          contextType: props.contextType,
          contextId: props.contextId,
          rights: props.rights,
          onSave: () => props.reload(props.role.id, props.contextId)
        }]
      }
    ]}
  >
    {isEmpty(props.rights) &&
      <ContentLoader
        fill={true}
        size="lg"
        description={trans('role_rights_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.rights) &&
      <RightsTable
        fill={true}
        rights={props.rights}
      />
    }
  </ContentSection>

RoleRights.propTypes = {
  id: T.string.isRequired,
  icon: T.string.isRequired,
  title: T.string.isRequired,
  disabled: T.bool,
  contextType: T.string.isRequired,
  contextId: T.string,
  role: T.shape(RoleTypes.propTypes),
  rights: T.object,
  reload: T.func.isRequired
}

export {
  RoleRights
}
