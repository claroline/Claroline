import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'

import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/community/prop-types'
import {selectors} from '#/main/log/account/functional/store/selectors'
import {FunctionalLogList} from '#/main/log/account/functional/components/list'

const FunctionalMain = (props) =>
  <UserPage
    showBreadcrumb={showBreadcrumb()}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('my_account'),
        target: '/account'
      }, {
        type: LINK_BUTTON,
        label: trans('functional'),
        target: '/account/functional'
      }
    ]}
    title={trans('functional')}
    user={props.currentUser}
  >
    <div style={{
      marginTop: 60 // TODO : manage spacing correctly
    }}>
      <FunctionalLogList
        name={selectors.STORE_NAME}
        url={['apiv2_logs_functional_list_current']}
      />
    </div>

  </UserPage>

FunctionalMain.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  FunctionalMain
}
