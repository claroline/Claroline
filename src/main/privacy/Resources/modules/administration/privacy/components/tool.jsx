import React from 'react'
import {ToolPage} from '#/main/core/tool/containers/page'
import {PrivacyItem} from '#/main/privacy/administration/privacy/components/privacy-item'
import {PrivacyLinkModals} from '#/main/privacy/administration/privacy/components/privacy-link-modals'

const PrivacyTool = (props) => {
  console.log('PrivacyTool', props)
  return(
    <ToolPage>
      <p>hello</p>

      <div className="privacy-item">
        <PrivacyItem item={props.parameters} />
        <PrivacyLinkModals item={props.parameters} />
      </div>
    </ToolPage>
  )
}

export {
  PrivacyTool
}
