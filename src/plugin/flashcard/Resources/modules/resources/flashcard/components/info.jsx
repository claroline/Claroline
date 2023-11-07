import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {ContentCounter} from '#/main/app/content/components/counter'
import {schemeCategory20c} from '#/main/theme/color/utils'

const FlashcardInfo = (props) =>
  <section className="d-flex flex-direction-row">
    <ContentCounter
      icon="fa fa-layer-group"
      label={trans('cartes', {}, 'flashcard')}
      color={schemeCategory20c[1]}
      value={props.flashcardProgression?.length}
    />

    <ContentCounter
      icon="fa fa-check"
      label={trans('check', {}, 'flashcard')}
      color={schemeCategory20c[10]}
      value={props.flashcardProgression?.filter((progression) => progression && progression.successCount > 0).length}
    />

    <ContentCounter
      icon="fa fa-xmark"
      label={trans('fail', {}, 'flashcard')}
      color={schemeCategory20c[5]}
      value={props.flashcardProgression?.filter((progression) => progression && progression.successCount === 0).length}
    />
  </section>

export {
  FlashcardInfo
}
