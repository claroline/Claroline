import React from 'react'
import {url} from '#/main/app/api'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {route} from '#/main/app/account/routing'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {User as UserTypes} from '#/main/community/prop-types'
import {AccountPage} from '#/main/app/account/containers/page'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {constants as actionConstants} from '#/main/app/action/constants'
import {LINK_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON, ASYNC_BUTTON} from '#/main/app/buttons'

import {PrivacySummary} from '#/main/privacy/component/summary'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/modals/terms-of-service'

const PrivacyMain = (props) =>
  <AccountPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('privacy'),
        target: route('privacy')
      }
    ]}
    title={trans('privacy')}
  >
    <ContentSizing size="md">
      <AlertBlock
        className="mb-0 mt-4"
        type={get(props.currentUser, 'meta.acceptedTerms') ? 'info' : 'warning'}
        title={get(props.currentUser, 'meta.acceptedTerms') ?
          trans('terms_of_service_accepted', {}, 'privacy') :
          trans('terms_of_service_not_accepted', {}, 'privacy')
        }
      >
        {!get(props.currentUser, 'meta.acceptedTerms') &&
          <Button
            className="btn"
            type={CALLBACK_BUTTON}
            label={trans('terms_of_service_accept', {}, 'privacy')}
            callback={() => props.acceptTerms()}
            primary={true}
          />
        }
        <Button
          className="btn"
          type={MODAL_BUTTON}
          label={trans('terms_of_service_show', {}, 'privacy')}
          modal={[MODAL_TERMS_OF_SERVICE]}
        />
      </AlertBlock>
      <PrivacySummary
        parameters={props.privacy}
      />

      <div className="row py-4 bg-body-tertiary">
        <div className="content-md">
          <h2 className="h-title h3">
            {trans('personal_data', {}, 'privacy')}
          </h2>
          <Button
            className="btn  btn-lg btn-primary w-100 mb-2"
            type={CALLBACK_BUTTON}
            label={trans('export_data', {}, 'privacy')}
            callback={props.exportAccount}
          />
          <Button
            className="btn btn-outline-danger w-100"
            type={ASYNC_BUTTON}
            label={trans('request_deletion', {}, 'privacy')}
            request={
              {
                url: url(['apiv2_user_request_account_deletion']),
                request:{method: 'POST', type: actionConstants.ACTION_SEND},
                messages: {
                  pending: {
                    title: trans('send.pending.title', {}, 'alerts'),
                    message: trans('send.pending.message', {}, 'alerts')
                  },
                  success: {
                    title: trans('send.success.title', {}, 'alerts'),
                    message: trans('send.success.message', {}, 'alerts')
                  }
                }
              }
            }
            dangerous={true}
            confirm={
              {
                title: trans('title_dialog_delete_account', {}, 'privacy'),
                message: trans('delete_account_message', {}, 'privacy')
              }
            }
          />
        </div>
      </div>
    </ContentSizing>
  </AccountPage>

PrivacyMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  privacy: T.shape({
    countryStorage: T.string,
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
    })
  }).isRequired,
  exportAccount: T.func.isRequired,
  acceptTerms: T.func.isRequired,
  messages: T.shape({
    pending: T.object,
    success: T.object,
    error: T.object
  })
}

export {
  PrivacyMain
}
