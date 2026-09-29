import React from 'react';

/* blueprint/import *//* SagatrashbinImportStart */import SagatrashbinWjgrguxfng from '@blueprint/extensions/sagatrashbin/TrashBinContainer';/* SagatrashbinImportEnd *//* VersionchangerImportStart */import VersionchangerYihfvzopxj from '@blueprint/extensions/versionchanger/VersionChangerContainer';/* VersionchangerImportEnd *//* MotdmakerImportStart */import MotdmakerZozllnhxqm from '@blueprint/extensions/motdmaker/MotdMakerContainer';/* MotdmakerImportEnd *//* ModrinthbrowserImportStart */import ModrinthbrowserSevkxckbbi from '@blueprint/extensions/modrinthbrowser/server/modrinth/ModrinthBrowserContainer';/* ModrinthbrowserImportEnd */

interface RouteDefinition {
  path: string;
  name: string | undefined;
  component: React.ComponentType;
  exact?: boolean;
  adminOnly: boolean | false;
  identifier: string;
}
interface ServerRouteDefinition extends RouteDefinition {
  permission: string | string[] | null;
}
interface Routes {
  account: RouteDefinition[];
  server: ServerRouteDefinition[];
}

export default {
  account: [
    /* routes/account *//* SagatrashbinAccountRouteStart *//* SagatrashbinAccountRouteEnd *//* VersionchangerAccountRouteStart *//* VersionchangerAccountRouteEnd *//* MotdmakerAccountRouteStart *//* MotdmakerAccountRouteEnd *//* ModrinthbrowserAccountRouteStart *//* ModrinthbrowserAccountRouteEnd */
  ],
  server: [
    /* routes/server *//* SagatrashbinServerRouteStart */{ path: '/files/trash-bin', permission: null, name: '', component: SagatrashbinWjgrguxfng, adminOnly: false, identifier: 'sagatrashbin' },/* SagatrashbinServerRouteEnd *//* VersionchangerServerRouteStart */{ path: '/minecraft/versions', permission: 'file.update', name: 'Versions', component: VersionchangerYihfvzopxj, adminOnly: false, identifier: 'versionchanger' },/* VersionchangerServerRouteEnd *//* MotdmakerServerRouteStart */{ path: '/motd-maker', permission: null, name: 'MOTD Maker', component: MotdmakerZozllnhxqm, adminOnly: false, identifier: 'motdmaker' },/* MotdmakerServerRouteEnd *//* ModrinthbrowserServerRouteStart */{ path: '/plugins', permission: null, name: 'Plugins', component: ModrinthbrowserSevkxckbbi, adminOnly: false, identifier: 'modrinthbrowser' },/* ModrinthbrowserServerRouteEnd */
  ],
} as Routes;
