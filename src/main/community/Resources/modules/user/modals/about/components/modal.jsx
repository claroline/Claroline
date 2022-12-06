import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {User as UserTypes} from '#/main/community/user/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'userId', 'user', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.user ? get(props.user, 'name') : trans('loading')}
    poster={get(props.user, 'poster')}
    onEntering={() => props.get(props.userId)}
    onExiting={() => props.reset()}
  >
    {!props.user &&
      <ContentLoader
        size="lg"
        description={trans('user_loading', {}, 'community')}
      />
    }

    {props.user &&
      <DetailsData
        data={props.user}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'meta.description',
                label: trans('description'),
                type: 'string'
              }, {
                name: 'organizations',
                type: 'organizations',
                label: trans('organizations')
              }, {
                name: 'id',
                label: trans('id'),
                type: 'string',
                calculated: (user) => user.id + ' / ' + user.autoId
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  userId: T.string.isRequired,
  user: T.shape(
    UserTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
