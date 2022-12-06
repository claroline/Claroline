import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentLoader} from '#/main/app/content/components/loader'
import {DetailsData} from '#/main/app/content/details/components/data'

import {Team as TeamTypes} from '#/main/community/team/prop-types'

const AboutModal = props =>
  <Modal
    {...omit(props, 'teamId', 'team', 'get', 'reset')}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.team ? get(props.team, 'name') : trans('loading')}
    poster={get(props.team, 'poster')}
    onEntering={() => props.get(props.teamId)}
    onExiting={() => props.reset()}
  >
    {!props.team &&
      <ContentLoader
        size="lg"
        description={trans('team_loading', {}, 'community')}
      />
    }

    {props.team &&
      <DetailsData
        data={props.team}
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
                name: 'directory',
                label: trans('directory', {}, 'resource'),
                type: 'resource',
                displayed: (team) => !!team.directory
              }, {
                name: 'role',
                label: trans('role', {}, 'community'),
                type: 'role'
              }, {
                name: 'managerRole',
                label: trans('manager_role', {}, 'community'),
                type: 'role'
              }, {
                name: 'id',
                label: trans('id'),
                type: 'string',
                calculated: (team) => team.id + ' / ' + team.autoId
              }
            ]
          }
        ]}
      />
    }
  </Modal>

AboutModal.propTypes = {
  teamId: T.string.isRequired,
  team: T.shape(
    TeamTypes.propTypes
  ),
  get: T.func.isRequired,
  reset: T.func.isRequired
}

export {
  AboutModal
}
