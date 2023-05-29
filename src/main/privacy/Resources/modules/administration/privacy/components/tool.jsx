import React from 'react'
import {ToolPage} from '#/main/core/tool/containers/page'
import {PrivacyItem} from '#/main/privacy/administration/privacy/components/privacy-item'

const PrivacyTool = (props) => {
  return(
    <ToolPage>
      <div className="privacy-item">
        {props.parameters.map((privacy, index) => (
          <PrivacyItem key={index} item={privacy} />
        ))}
      </div>
    </ToolPage>
  )}

export {
  PrivacyTool
}
