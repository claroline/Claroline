/**
 * The application top bar.
 *
 * NB.
 * This is a standard app for now because it's out of our main react context.
 * It will be removed when the full app container will be available.
 */

import {bootstrap} from '#/main/app/bootstrap'

import {Header} from '#/main/app/overlay/header/containers/header'
import {reducer} from '#/main/app/overlay/header/store'

bootstrap(
  '.app-header-container',
  Header,
  reducer
)
