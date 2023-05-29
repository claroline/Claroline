import React from 'react'
import {PropTypes as T} from 'prop-types'
import {PageFull} from '#/main/app/page/components/full'

import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {PrivacyItem} from '#/main/privacy/administration/privacy/components/privacy-item'
import {PrivacyLinkModals} from '#/main/privacy/administration/privacy/components/privacy-link-modals'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {trans} from '#/main/app/intl/translation'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import get from 'lodash/get'

const PrivacyTool = (props) => {
  console.log(props.parameters)
  return(
    <ToolPage>
      <div className="privacy-item">
        {props.parameters.map((privacy, index) => (
          <PrivacyItem key={index} item={privacy} />
        ))}
      </div>
      <div className="privacy-links-modals">
        {props.parameters.map((privacy, index) => (
          <PrivacyLinkModals key={index} parameters={privacy} />
        ))}
      </div>
    </ToolPage>
  )}

export {
  PrivacyTool
}
