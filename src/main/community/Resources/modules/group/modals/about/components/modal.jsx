import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Group as GroupTypes} from '#/main/community/group/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'groupId', 'group', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.group ? get(props.group, 'name') : trans('loading')}
    poster={get(props.group, 'poster')}
    onEntering={() => props.get(props.groupId)}
    onExiting={() => props.reset()}
  >
    {!props.group &&
      <ContentLoader
        size="lg"
        description={trans('group_loading', {}, 'community')}
      />
    }

    {props.group &&
      <DetailsData
        data={props.group}
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
                calculated: (group) => group.id + ' / ' + group.autoId
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  groupId: T.string.isRequired,
  group: T.shape(
    GroupTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
