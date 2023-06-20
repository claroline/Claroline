import React from 'react'
import { ToolPage } from '#/main/core/tool/containers/page'
import { PrivacyItem } from '#/main/privacy/administration/privacy/components/privacy-item'
import { PrivacyLinkModals } from '#/main/privacy/administration/privacy/components/privacy-link-modals'

const PrivacyTool = (props) =>
  <ToolPage>
    <PrivacyItem item={props.parameters} />
    {props.isAdmin && <PrivacyLinkModals item={props.parameters} />}
  </ToolPage>

export {
  PrivacyTool
}
