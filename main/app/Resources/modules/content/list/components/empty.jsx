import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

const ListEmpty = props =>
  <div className="list-empty">
    <div className="list-empty-info">
      <span className="list-empty-icon fa fa-refresh" />

      <div className="list-empty-content">
        {t(props.hasFilters ? 'list_search_no_results' : 'list_no_results')}

        {props.contentDesc &&
          <p className="list-content-desc">
            {props.contentDesc}
          </p>
        }
      </div>
    </div>

    {/*<div className="list-empty-actions">
      <button type="button" className="btn btn-block btn-link">
        <span className="fa fa-fw fa-search" />
        Changer votre recherche
      </button>

      <button type="button" className="btn btn-block btn-primary">
        <span className="fa fa-fw fa-plus" />
        Créer un nouveau lieu
      </button>
    </div>*/}
  </div>

ListEmpty.propTypes = {
  contentDesc: T.string,
  hasFilters: T.bool
}

ListEmpty.defaultProps = {
  hasFilters: false
}

export {
  ListEmpty
}
