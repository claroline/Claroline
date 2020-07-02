import {createSelector} from 'reselect'

import {selectors as cursusSelectors} from '#/plugin/cursus/tools/cursus/store/selectors'

const catalog = createSelector(
  [cursusSelectors.store],
  (store) => store.catalog
)

const session = createSelector(
  [catalog],
  (catalog) => catalog.session
)

const sessionUser = createSelector(
  [catalog],
  (catalog) => catalog.sessionUser
)

const sessionQueue = createSelector(
  [catalog],
  (catalog) => catalog.sessionQueue
)

const isFull = createSelector(
  [catalog],
  (catalog) => catalog.isFull
)

const eventsRegistration = createSelector(
  [catalog],
  (catalog) => catalog.eventsRegistration
)

export const selectors = {
  catalog,
  session,
  sessionUser,
  sessionQueue,
  isFull,
  eventsRegistration
}