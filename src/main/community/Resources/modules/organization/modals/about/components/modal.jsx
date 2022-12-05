import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {constants} from '#/main/community/organization/constants'

const AboutModal = props =>
  <Modal
    {...omit(props, 'organizationId', 'organization', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.organization ? get(props.organization, 'name') : trans('loading')}
    poster={get(props.organization, 'poster')}
    onEntering={() => props.get(props.organizationId)}
    onExiting={() => props.reset()}
  >
    {!props.organization &&
      <ContentLoader
        size="lg"
        description={trans('organization_loading', {}, 'community')}
      />
    }

    {props.organization &&
      <DetailsData
        data={props.organization}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'parent',
                label: trans('parent'),
                type: 'organization',
                displayed: (organization) => !!organization.parent
              }, {
                name: 'meta.description',
                label: trans('description'),
                type: 'string'
              }, {
                name: 'type',
                type: 'choice',
                label: trans('type'),
                options: {
                  choices: constants.ORGANIZATION_TYPES
                }
              }, {
                name: 'email',
                type: 'email',
                label: trans('email')
              }, {
                name: 'vat',
                label: trans('vat_number'),
                type: 'string',
                required: false
              }, {
                name: 'id',
                label: trans('id'),
                type: 'string',
                calculated: (organization) => organization.id + ' / ' + organization.autoId
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  organizationId: T.string.isRequired,
  organization: T.shape(
    OrganizationTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
