import React, { useEffect, useState } from 'react'
import { PropTypes as T } from 'prop-types'
import get from 'lodash/get'

import { trans } from '#/main/app/intl/translation'
import { Button } from '#/main/app/action/components/button'
import { LINK_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON, ASYNC_BUTTON } from '#/main/app/buttons'
import { AlertBlock } from '#/main/app/alert/components/alert-block'
import { ContentTitle } from '#/main/app/content/components/title'
import { MODAL_TERMS_OF_SERVICE } from '#/main/app/modals/terms-of-service'
import { AccountPage } from '#/main/app/account/containers/page'
import { route } from '#/main/app/account/routing'
import { User as UserTypes } from '#/main/community/prop-types'
import { url } from '#/main/app/api'
import { constants as actionConstants } from '#/main/app/action/constants'
import { ContentLoader } from '#/main/app/content/components/loader'

const PrivacyMain = (props) => {
  const undefinedValue = trans('undefined', {}, 'privacy')
  const [dataLoaded, setDataLoaded] = useState(false)

  useEffect(() => {
    const fetchData = async () => {
      await props.fetch()
      setDataLoaded(true)
    }

    fetchData()
  }, [props.fetch])

  return (
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
        title={trans('terms_of_service', {}, 'privacy')}
        style={{ marginTop: 60 }}
      />

      <AlertBlock
        type={get(props.currentUser, 'meta.acceptedTerms') ? 'info' : 'warning'}
        title={get(props.currentUser, 'meta.acceptedTerms')
          ? trans('accept_terms', {}, 'privacy')
          : trans('no_accept_terms', {}, 'privacy')}
      >
        {!get(props.currentUser, 'meta.acceptedTerms') && (
          <Button
            className="btn"
            type={CALLBACK_BUTTON}
            label={trans('accept-terms-of-service', {}, 'actions')}
            callback={() => props.acceptTerms()}
            primary={true}
          />
        )}

        <Button
          className="btn"
          type={MODAL_BUTTON}
          label={trans('show-terms-of-service', {}, 'actions')}
          modal={[MODAL_TERMS_OF_SERVICE]}
        />
      </AlertBlock>

      <ContentTitle title={trans('dpo')} />

      {!props.loaded && (
        <ContentLoader size="sm" description={trans('loading', {}, 'privacy')} />
      )}

      {(props.loaded && props.privacyParameters) && dataLoaded && (
        props.privacyParameters.dpo && (
          <ul>
            <li><strong>{trans('name')} :</strong><br/>
              {props.privacyParameters.dpo.name ? props.privacyParameters.dpo.name : undefinedValue}</li>
            <li><strong>{trans('email')} :</strong><br/>
              {props.privacyParameters.dpo.email ? props.privacyParameters.dpo.email : undefinedValue}</li>
            <li><strong>{trans('phone')} :</strong><br/>
              {props.privacyParameters.dpo.phone ? props.privacyParameters.dpo.phone : undefinedValue}</li>
            <li><strong>{trans('address')} :</strong><br/>
              {props.privacyParameters.dpo.address.street1 ? props.privacyParameters.dpo.address.street1 : undefinedValue}<br/>
              {props.privacyParameters.dpo.address.street2 ? props.privacyParameters.dpo.address.street2 : undefinedValue}<br/>
              {props.privacyParameters.dpo.address.postalCode ? props.privacyParameters.dpo.address.postalCode : undefinedValue}
              {props.privacyParameters.dpo.address.city ? props.privacyParameters.dpo.address.city : undefinedValue}<br/>
              {props.privacyParameters.dpo.address.country ? props.privacyParameters.dpo.address.country : undefinedValue}<br/>
              {props.privacyParameters.dpo.address.state ? props.privacyParameters.dpo.address.state : undefinedValue}
            </li>
          </ul>
        )
      )}

      {(props.loaded && props.privacyParameters) && dataLoaded && (
        props.privacyParameters.countryStorage && (
          <p>
            <strong>{trans('country_storage', {}, 'privacy')} : </strong><br/>
            {props.privacyParameters.countryStorage ? props.privacyParameters.countryStorage : undefinedValue}
          </p>
        )
      )}

      <ContentTitle title={trans('title_my_data', {}, 'privacy')} />

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
          request: { method: 'POST', type: actionConstants.ACTION_SEND },
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
        }}
        dangerous={true}
        confirm={{
          title: trans('title_dialog_delete_account', {}, 'privacy'),
          message: trans('message_dialog_delete_account', {}, 'privacy')
        }}
      />
    </AccountPage>
  )
}

PrivacyMain.propTypes = {
  currentUser: T.shape(UserTypes.propTypes).isRequired,
  exportAccount: T.func.isRequired,
  acceptTerms: T.func.isRequired,
  messages: T.shape({
    pending: T.object,
    success: T.object,
    error: T.object
  }),
  loaded: T.bool.isRequired,
  fetch: T.func.isRequired,
  privacyParameters: T.shape({
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
  }).isRequired
}

export {
  PrivacyMain
}
