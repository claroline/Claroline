export const LIST_PROP_DISPLAYED   = 1
export const LIST_PROP_DISPLAYABLE = 2
export const LIST_PROP_FILTERABLE  = 4
export const LIST_PROP_SORTABLE    = 8

export const LIST_PROP_DEFAULT = LIST_PROP_DISPLAYED|LIST_PROP_SORTABLE|LIST_PROP_FILTERABLE|LIST_PROP_DISPLAYABLE

export const isPropDisplayable = (prop) => !prop.flags || (prop.flags&LIST_PROP_DISPLAYABLE)
export const isPropDisplayed   = (prop) => !prop.flags || (prop.flags&LIST_PROP_DISPLAYED)
export const isPropFilterable  = (prop) => !prop.flags || (prop.flags&LIST_PROP_FILTERABLE)
export const isPropSortable    = (prop) => !prop.flags || (prop.flags&LIST_PROP_SORTABLE)

export function getListDisplay(available, format) {
  return available.find(availableFormat => availableFormat[0] === format)
}

export function getDisplayableProps(dataProps) {
  return dataProps.filter(prop => isPropDisplayable(prop))
}

export function getDisplayedProps(dataProps) {
  return dataProps.filter(prop => isPropDisplayed(prop))
}

export function getFilterableProps(dataProps) {
  return dataProps.filter(prop => isPropFilterable(prop))
}

export function getSortableProps(dataProps) {
  return dataProps.filter(prop => isPropSortable(prop))
}

export function getPropDefinition(propName, dataProps) {
  return dataProps.find(prop => propName === prop.name)
}
