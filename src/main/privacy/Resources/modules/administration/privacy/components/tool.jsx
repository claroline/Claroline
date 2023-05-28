import React from 'react'
import {PropTypes as T} from 'prop-types'

import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {trans} from '#/main/app/intl/translation'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

const PrivacyTool = (props) => {
  console.log(props)
  return(
    <ToolPage>


    
    </ToolPage>
  )}

export {
  PrivacyTool
}
