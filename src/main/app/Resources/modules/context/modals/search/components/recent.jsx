import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'

import {getRecent, removeRecent} from '#/main/app/history'
import isEmpty from 'lodash/isEmpty'
import {trans} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

function parseRecent(recent) {
  return Object.keys(recent).map(recentId => recent[recentId])
}

const SearchRecent = (props) => {
  let recent = getRecent()
  const [history, setHistory] = useState(parseRecent(recent))

  if (isEmpty(recent)) {
    return (
      <div className="text-center mt-3" role="presentation">
        <p className="lead mb-1">{trans('no_history_results')}</p>
        <p className="mb-0 text-secondary">{trans('no_history_results_help')}</p>
      </div>
    )
  }

  return (
    <div className="mt-3" role="presentation">
      <h5 className="fs-sm text-uppercase text-body-secondary">{trans('Récent')}</h5>
      <div className="d-flex flex-column gap-2" role="presentation">
        {history
          .sort((a, b) => a.date > b.date ? -1 : 1)
          .map(result => (
            <DataCard
              key={result.id}
              id={result.id}
              size="xs"
              direction="row"
              title={result.name}
              contentText={result.description}
              poster={result.thumbnail}
              icon={!result.thumbnail ? <>{result.name.charAt(0)}</> : null}
              primaryAction={{
                type: LINK_BUTTON,
                label: trans('open', {}, 'actions'),

                target: result.target,
                onClick: props.fadeModal
              }}
              actions={[
                {
                  name: 'delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-times',
                  label: trans('delete', {}, 'actions'),
                  callback: () => {
                    const newRecent = removeRecent(result.id)
                    setHistory(parseRecent(newRecent))
                  }
                }
              ]}
            />
          ))
        }
      </div>
    </div>
  )
}

SearchRecent.propTypes = {
  fadeModal: T.func.isRequired
}

export {
  SearchRecent
}
