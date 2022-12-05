import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {constants} from '#/main/community/constants'

const AboutModal = props =>
  <Modal
    {...omit(props, 'roleId', 'role', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={trans(get(props.role, 'translationKey', 'loading'))}
    onEntering={() => props.get(props.roleId)}
    onExiting={() => props.reset()}
  >
    {!props.role &&
      <ContentLoader
        size="lg"
        description={trans('role_loading', {}, 'community')}
      />
    }

    {props.role &&
      <DetailsData
        data={props.role}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'name',
                type: 'string',
                label: trans('code')
              }, {
                name: 'meta.description',
                label: trans('description'),
                type: 'string'
              }, {
                name: 'type',
                type: 'choice',
                label: trans('type'),
                options: {
                  choices: constants.ROLE_TYPES
                },
                linked: [
                  {
                    name: 'workspace',
                    type: 'workspace',
                    label: trans('workspace'),
                    displayed: constants.ROLE_WORKSPACE === props.role.type
                  }, {
                    name: 'user',
                    type: 'user',
                    label: trans('user'),
                    displayed: constants.ROLE_USER === props.role.type
                  }
                ]
              }, {
                name: 'id',
                label: trans('id'),
                type: 'string',
                calculated: (role) => role.id + ' / ' + role.autoId
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  roleId: T.string.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
