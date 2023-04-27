import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON, ASYNC_BUTTON} from '#/main/app/buttons'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ContentTitle} from '#/main/app/content/components/title'
import {DetailsData} from '#/main/app/content/details/components/data'
import {MODAL_TERMS_OF_SERVICE} from '#/main/app/modals/terms-of-service'
import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'
import {User as UserTypes} from '#/main/community/prop-types'

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
      title={trans('terms_of_service')}
      style={{marginTop: 60}}
    />

    <AlertBlock
      type={get(props.currentUser, 'meta.acceptedTerms') ? 'info' : 'warning'}
      title={get(props.currentUser, 'meta.acceptedTerms') ?
        'Vous avez accepté les conditions d\'utilisation de la plateforme.' :
        'Vous n\'avez pas encore accepté les conditions d\'utilisation de la plateforme.'
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

    <DetailsData
      data={props.privacy}
      sections={[
        {
          title: trans('dpo'),
          fields: [
            {
              name: 'dpo.name',
              label: trans('name'),
              type: 'string'
            }, {
              name: 'dpo.email',
              label: trans('email'),
              type: 'email'
            }, {
              name: 'dpo.phone',
              label: trans('phone'),
              type: 'string'
            }, {
              name: 'dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }
      ]}
    />

    <ContentTitle
      title="Mes données"
    />

    <Button
      className="btn btn-block component-container"
      type={CALLBACK_BUTTON}
      label={trans('Exporter mes données')}
      callback={props.exportAccount}
    />

    <Button
      className="btn btn-block component-container"
      type={ASYNC_BUTTON}
      label={trans('Demander la suppression de mon compte')}
      request={{
        method: 'SEND_MAIL',
        url: route('dataUser.delete')
      }}
      dangerous={true}
      alert={trans('Un email va être envoyé au DPO pour la supression de vos données personnelles. Etes-vous sûr de vouloir valider cette action ?')}
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
  acceptTerms: T.func.isRequired
}

export {
  PrivacyMain
}
