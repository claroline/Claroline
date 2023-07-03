import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON, ASYNC_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentTitle} from '#/main/app/content/components/title'
import {MODAL_TERMS_OF_SERVICE} from '#/main/app/modals/terms-of-service'
import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'
import {User as UserTypes} from '#/main/community/prop-types'
import {url} from '#/main/app/api'
import {constants as actionConstants} from '#/main/app/action/constants'

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
    <ContentTitle
      title={trans('terms_of_service',{}, 'privacy')}
      style={{marginTop: 60}}
    />

    <AlertBlock
      type={get(props.currentUser, 'meta.acceptedTerms') ? 'info' : 'warning'}
      title={get(props.currentUser, 'meta.acceptedTerms') ?
        trans('accept_terms', {}, 'privacy') :
        trans('no_accept_terms', {}, 'privacy')
      }
    >
      {!get(props.currentUser, 'meta.acceptedTerms') &&
        <Button
          className="btn"
          type={CALLBACK_BUTTON}
          label={trans('accept-terms-of-service', {}, 'actions')}
          callback={() => props.acceptTerms()}
          primary={true}
        />
      }

      <Button
        className="btn"
        type={MODAL_BUTTON}
        label={trans('show-terms-of-service', {}, 'actions')}
        modal={[MODAL_TERMS_OF_SERVICE]}
      />
    </AlertBlock>

    <ContentTitle
      title={trans('dpo')}
    />

    {/* AFFICHAGE DONNEES DPO */}

    <ContentTitle
      title={trans('title_my_data', {}, 'privacy')}
    />

    <Button
      className="btn btn-block component-container"
      type={CALLBACK_BUTTON}
      label={trans('export_data', {}, 'privacy')}
      callback={props.exportAccount}
    />

    <Button
      className="btn btn-block component-container"
      type={ASYNC_BUTTON}
      label={trans('request_deletion', {}, 'privacy')}
      request={{
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
        }}
      }
      dangerous={true}
      confirm={{
        title: trans('title_dialog_delete_account', {}, 'privacy'),
        message: trans('message_dialog_delete_account', {}, 'privacy')
      }}
    />
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