import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageSimple} from '#/main/app/page/components/simple'
import {ContentHelp} from '#/main/app/content/components/help'

const HomeDisabled = (props) =>
  <PageSimple>
    DISABLED

    {!props.authenticated &&
      <Button
        type={LINK_BUTTON}
        className="btn btn-block btn-emphasis"
        label={trans('login', {}, 'actions')}
        primary={true}
        target="/login"
      />
    }

    {!props.authenticated &&
      <ContentHelp help={trans('only_admin_login_help', {}, 'administration')} />
    }

    {props.authenticated &&
      <Button
        className="btn btn-block btn-emphasis"
        type={CALLBACK_BUTTON}
        label={trans('Réactiver', {}, 'actions')}
        callback={() => true}
        primary={true}
      />
    }
  </PageSimple>

HomeDisabled.propTypes = {
  disabled: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  maintenance: T.bool.isRequired,
  maintenanceMessage: T.string,
  restrictions: T.shape({
    disabled: T.bool,
    dates: T.arrayOf(T.string)
  })
}

export {
  HomeDisabled
}
