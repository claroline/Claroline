import {trans} from '#/main/app/intl/translation'

const HistoryList = {
  definition: [
    {
      name: 'id',
      type: 'string',
      label: trans('id'),
      displayed: true,
      primary: true
    },
    {
      name: 'log',
      type: 'string',
      label: trans('log')
    },
    {
      name: 'status',
      type: 'string',
      label: trans('status'),
      displayed: true
    },
    {
      name: 'uploadDate',
      type: 'date',
      label: trans('upload_date'),
      displayed: true
    }
  ]
}

export {
  HistoryList
}
