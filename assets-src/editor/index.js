/**
 * ShubhamTiwari SEO Tools block editor entry point: registers the sidebar.
 */
import { registerPlugin } from '@wordpress/plugins';

import Sidebar from './sidebar';
import './editor.scss';

registerPlugin( 'shubhamtiwari-seo-tools', { render: Sidebar } );
