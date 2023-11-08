import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'
import {schemeCategory20c} from '#/main/theme/color/utils'

const FlashcardInfo = (props) => {
  const getLength = (items) => items ? items.length : 0
  const countSuccess = (items) => items ? items.filter((item) => item && item.successCount > 0).length : 0
  const countFail = (items) => items ? items.filter((item) => item && item.successCount === 0).length : 0

  return (
    <section className="d-flex flex-direction-row">
      <ContentCounter
        icon="fa fa-layer-group"
        label={trans('cartes', {}, 'flashcard')}
        color={schemeCategory20c[1]}
        value={getLength(props.flashcardProgression)}
      />

      <ContentCounter
        icon="fa fa-check"
        label={trans('check', {}, 'flashcard')}
        color={schemeCategory20c[10]}
        value={countSuccess(props.flashcardProgression)}
      />

      <ContentCounter
        icon="fa fa-xmark"
        label={trans('fail', {}, 'flashcard')}
        color={schemeCategory20c[5]}
        value={countFail(props.flashcardProgression)}
      />
    </section>
  )
}

export {
  FlashcardInfo
}
