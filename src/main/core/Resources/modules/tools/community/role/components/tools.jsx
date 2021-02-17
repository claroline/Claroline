import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Checkbox} from '#/main/app/input/components/checkbox'

const ToolRightsRow = props =>
  <div className="tool-rights-row list-group-item">
    <div className="tool-rights-title">
      {trans(props.toolName, {}, 'tools')}
    </div>

    <div className="tool-rights-actions">
      {Object.keys(props.permissions).map((permName) =>
        <Checkbox
          key={permName}
          id={`${props.toolName}-${permName}`}
          label={trans(permName, {}, 'actions')}
          checked={props.permissions[permName]}
          disabled={true}
          onChange={() => {}}
        />
      )}
    </div>
  </div>

ToolRightsRow.propTypes = {
  toolName: T.string.isRequired,
  permissions: T.object
}

const RoleTools = (props) =>
  <div className="list-group" fill={true}>
    {Object.keys(props.tools || {}).map(toolName =>
      <ToolRightsRow
        key={`tool-rights-${toolName}`}
        toolName={toolName}
        permissions={props.tools[toolName]}
      />
    )}
  </div>

RoleTools.propTypes = {
  tools: T.object
}

export {
  RoleTools
}
