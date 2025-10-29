import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'

import {trans} from '#/main/app/intl'
import {useFetch, actions as fetchActions, constants as fetchConst} from '#/main/app/api/fetch'
import {Button} from '#/main/app/action'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/components/alert'
import {DataMicro} from '#/main/app/data/components/micro'

import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'

const LinkedOrganizationsSkeleton = () =>
  <ul className="list-group list-group-flush mb-0 border-top border-bottom">
    <li className="list-group-item d-flex align-items-center gap-3 px-0">
      <DataMicro loaded={false} object={{}} />
    </li>
    <li className="list-group-item d-flex align-items-center gap-3 px-0">
      <DataMicro loaded={false} object={{}} />
    </li>
    <li className="list-group-item d-flex align-items-center gap-3 px-0">
      <DataMicro loaded={false} object={{}} />
    </li>
    <li className="list-group-item py-1 px-0">
      <Button
        className="btn-add btn btn-link text-start ms-n2 px-2"
        type={CALLBACK_BUTTON}
        icon="fa fa-plus"
        label={trans('add_organizations', {}, 'actions')}
        callback={() => true}
        disabled={true}
      />
    </li>
  </ul>

const LinkedOrganizations = ({
  name,
  description,
  url,
  addUrl,
  removeUrl,
  autoload = true
}) => {
  const dispatch = useDispatch()
  const [data, status] = useFetch(name, url, {autoload: autoload})

  console.log(data)
  console.log(status)

  if (fetchConst.STATUS_IDLE === status || fetchConst.STATUS_PENDING === status) {
    return (
      <LinkedOrganizationsSkeleton />
    )
  }

  if (fetchConst.STATUS_FAILED === status) {
    return (
      <Alert type="danger">
        {trans('organizations_loading_error', {}, 'community')}
      </Alert>
    )
  }

  return (
    <ul className="list-group list-group-flush mb-0 border-top border-bottom">
      {data.map(organization => (
        <li key={organization.id} className="list-group-item d-flex align-items-center gap-3 px-0">
          <DataMicro object={organization} />

          <Button
            className="btn btn-link ms-auto me-n2"
            size="sm"
            {...{
              name: 'remove',
              type: ASYNC_BUTTON,
              label: trans('remove', {}, 'actions'),
              request: {
                url: removeUrl,
                request: {
                  method: 'DELETE',
                  body: JSON.stringify([organization.id])
                },
                success: (response) => dispatch(fetchActions.reload(name, response))
              }
            }}
          />
        </li>
      ))}

      <li className="list-group-item py-1 px-0">
        <Button
          className="btn-add btn btn-link text-start ms-n2 px-2"
          type={MODAL_BUTTON}
          icon="fa fa-plus"
          label={trans('add_organizations', {}, 'actions')}
          modal={[MODAL_ORGANIZATIONS, {
            subtitle: description,
            multiple: true,
            selectAction: (organizations) => ({
              type: ASYNC_BUTTON,
              label: trans('add', {}, 'actions'),
              request: {
                url: addUrl,
                request: {
                  method: 'PATCH',
                  body: JSON.stringify(organizations.map(o => o.id))
                },
                success: (response) => dispatch(fetchActions.reload(name, response))
              }
            })
          }]}
        />
      </li>
    </ul>
  )
}

LinkedOrganizations.propTypes = {
  autoload: T.bool,
  name: T.string.isRequired,
  description: T.string,
  url: T.oneOfType([T.string, T.array]).isRequired,
  addUrl: T.oneOfType([T.string, T.array]).isRequired,
  removeUrl: T.oneOfType([T.string, T.array]).isRequired
}

export {
  LinkedOrganizations
}