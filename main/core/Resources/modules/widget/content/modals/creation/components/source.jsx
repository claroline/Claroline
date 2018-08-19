import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const ContentSource = props =>
  <div className="list-group">
    {props.sources.map(source =>
      <Button
        key={source.id}
        className="list-group-item"
        type={CALLBACK_BUTTON}
        label={trans(source.name, {}, 'data_sources')}
        callback={() => props.select(source)}
      />
    )}
  </div>

ContentSource.propTypes = {
  sources: T.arrayOf(T.shape({

  })).isRequired,
  select: T.func.isRequired
}

export {
  ContentSource
}
