import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import {useDispatch} from 'react-redux'

import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans}from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {Alert} from '#/main/app/components/alert'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {PageContent} from '#/main/app/page'
import {Tool} from '#/main/core/tool'

import {PrivacySummary} from '#/main/privacy/components/summary'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/modals/terms-of-service'
import {MODAL_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country-storage'
import {MODAL_TOS_EDITOR} from '#/main/privacy/administration/privacy/modals/terms-of-service'
import {actions} from '#/main/privacy/administration/privacy/store'

const PrivacyTool = (props) => {
  const dispatch = useDispatch()

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
        <PageContent className="py-5">
          <ContentSizing size="lg">
            <PrivacySummary
              className="mb-4"
              dpo={props.parameters.dpo || {}}
              countryStorage={props.parameters.countryStorage}
            />

            {get(props.parameters, 'tos.enabled') &&
              <Button
                className="btn btn-lg btn-primary w-100 mb-4"
                type={MODAL_BUTTON}
                label={trans('terms_of_service_show', {}, 'privacy')}
                modal={[MODAL_TERMS_OF_SERVICE]}
              />
            }

            <Alert
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
                  modal={[MODAL_TOS_EDITOR, {
                    tos: props.parameters.tos,
                    onSave: (updatedData) => dispatch(actions.loadPrivacyParameters(updatedData))
                  }]}
                />
              </div>
            </Alert>

            <Alert
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
                  modal={[MODAL_DPO, {
                    dpo: props.parameters.dpo,
                    onSave: (updatedData) => dispatch(actions.loadPrivacyParameters(updatedData))
                  }]}
                />
              </div>
            </Alert>

            <Alert
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
                  modal={[MODAL_COUNTRY_STORAGE, {
                    countryStorage: props.parameters.countryStorage,
                    onSave: (updatedData) => dispatch(actions.loadPrivacyParameters(updatedData))
                  }]}
                />
              </div>
            </Alert>
          </ContentSizing>
        </PageContent>
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
    countryStorage: T.string
  })
}

export {
  PrivacyTool
}
