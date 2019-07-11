import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

const TokenList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/tokens/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'token',
      type: 'string',
      label: trans('token'),
      displayed: true,
      primary: true
    },
    {
      name: 'description',
      type: 'string',
      label: trans('description'),
      displayed: true
    }
  ]
}

export {
  TokenList
}
