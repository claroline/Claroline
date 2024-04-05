import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans}from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {PrivacySummary} from '#/main/privacy/component/summary'
import {MODAL_DPO} from '#/main/privacy/modals/dpo'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/modals/country-storage'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/modals/terms-of-service'
import {MODAL_TOS_EDITOR} from '#/main/privacy/modals/terms-of-service/editor'
import {Tool} from '#/main/core/tool'

const PrivacyTool = (props) => {
  const isDpoFilled = props.parameters.dpo && props.parameters.dpo.name &&
    props.parameters.dpo.email && props.parameters.dpo.address.street1 &&
    props.parameters.dpo.address.postalCode &&
    props.parameters.dpo.address.city &&
    props.parameters.dpo.address.country

  return (
    <Tool
      {...props}
    >
      <ToolPage>
        <ContentSizing size="md">
          <PrivacySummary
            parameters={props.parameters}
          />
          {get(props.parameters, 'tos.enabled') &&
            <Button
              className="btn btn-lg btn-primary w-100 mb-3"
              type={MODAL_BUTTON}
              label={trans('terms_of_service_show', {}, 'privacy')}
              modal={[MODAL_TERMS_OF_SERVICE]}
            />
          }
          <AlertBlock
            type={get(props.parameters, 'tos.enabled') ? 'success' : 'danger'}
            title={trans('terms_of_service', {}, 'privacy')}
          >
            {get(props.parameters, 'tos.enabled') ?
              trans('terms_of_service_alert_enabled', {}, 'privacy') :
              trans('terms_of_service_alert_disabled', {}, 'privacy')
            }
            <div className="btn-toolbar gap-1 mt-3 justify-content-end">
              <Button
                className={`btn btn-${get(props.parameters, 'tos.enabled') ? 'success' : 'danger'}`}
                type={MODAL_BUTTON}
                label={get(props.parameters, 'tos.enabled') ?
                  trans('edit', {}, 'actions') :
                  trans('terms_of_service_activation', {}, 'privacy')
                }
                modal={[MODAL_TOS_EDITOR]}
              />
            </div>
          </AlertBlock>
          <AlertBlock
            type={isDpoFilled ? 'success' : 'danger'}
            title={trans('dpo', {}, 'privacy')}
          >
            {isDpoFilled ?
              trans('dpo_alert_enabled', {}, 'privacy') :
              trans('dpo_alert_disabled', {}, 'privacy')
            }
            <div className="btn-toolbar gap-1 mt-3 justify-content-end">
              <Button
                className={`btn btn-${isDpoFilled ? 'success' : 'danger'}`}
                type={MODAL_BUTTON}
                label={trans('edit', {}, 'actions')}
                modal={[MODAL_DPO]}
              />
            </div>
          </AlertBlock>
          <AlertBlock
            type={props.parameters.countryStorage ? 'success' : 'danger'}
            title={trans('country_storage', {}, 'privacy')}
          >
            {props.parameters.countryStorage ?
              trans('country_storage_alert_enabled', {}, 'privacy') :
              trans('country_storage_alert_disabled', {}, 'privacy')
            }
            <div className="btn-toolbar gap-1 mt-3 justify-content-end">
              <Button
                className={`btn btn-${props.parameters.countryStorage ? 'success' : 'danger'}`}
                type={MODAL_BUTTON}
                label={trans('edit', {}, 'actions')}
                modal={[MODAL_COUNTRY_STORAGE]}
              />
            </div>
          </AlertBlock>
        </ContentSizing>
      </ToolPage>
    </Tool>
  )
}

PrivacyTool.propTypes = {
  path: T.string,
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    }),
    dpo: T.shape({
      name: T.string,
      email: T.string,
      address: T.shape({
        street1: T.string,
        street2: T.string,
        postalCode: T.string,
        city: T.string,
        state: T.string,
        country: T.string
      }),
      phone: T.string
    }),
    countryStorage: T.bool
  })
}

PrivacyTool.defaultProps = {
  parameters: {
    dpo: {},
    tos: {}
  }
}

export {
  PrivacyTool
}
