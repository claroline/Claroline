import React from 'react'
import {ToolPage} from '#/main/core/tool/containers/page'
import {PrivacyItem} from '#/main/privacy/administration/privacy/components/privacy-item'
import {PrivacyLinkModals} from '#/main/privacy/administration/privacy/components/privacy-link-modals'

const PrivacyTool = (props) => {
  console.log('PrivacyTool', props.parameters.parameters.data)
  return(
    <ToolPage>
      <div className="privacy-item">
        <PrivacyItem item={props.parameters.parameters.data} />
        <PrivacyLinkModals item={props.parameters.parameters.data} />
      </div>
    </ToolPage>
  )
}

export {
  PrivacyTool
}
