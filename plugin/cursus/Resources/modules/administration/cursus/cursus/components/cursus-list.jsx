import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {CursusCard} from '#/plugin/cursus/administration/cursus/cursus/data/components/cursus-card'

const CursusList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/cursus/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'title',
      type: 'string',
      label: trans('title'),
      displayed: true,
      primary: true
    }, {
      name: 'code',
      type: 'string',
      label: trans('code'),
      displayed: true
    }, {
      name: 'meta.blocking',
      alias: 'blocking',
      type: 'boolean',
      label: trans('blocking', {}, 'cursus'),
      displayed: true
    }
  ],
  card: CursusCard
}

export {
  CursusList
}
