import {createRouter, createWebHistory} from "vue-router";

import Home from "@/Components/HomeView.vue";
import About from "@/Components/AboutView.vue";
import Artists from "@/Components/ArtistsView.vue";
import ArtistDetail from "@/Components/ArtistDetailView.vue";
import Gigs from "@/Components/GigsView.vue";
import Blog from "@/Components/BlogView.vue";
import BlogPost from "@/Components/BlogPostView.vue";
import Contact from "@/Components/ContactView.vue";
import MeetTheTeam from "@/Components/MeetTheTeam.vue";
import LoginView from "@/Components/LoginView.vue";

const routes = [
	{
		path: "/",
		name: "home",
		component: Home,
		meta: {
			layout: "public",
		},
	},
	{
		path: "/about",
		name: "about",
		component: About,
	},
	{
		path: "/about/team",
		name: "meet-the-team",
		component: MeetTheTeam,
	},
	{
		path: "/artists",
		name: "artists",
		component: Artists,
	},
	{
		path: "/artists/:slug",
		name: "artist",
		component: ArtistDetail,
	},
	{
		path: "/gigs",
		name: "gigs",
		component: Gigs,
	},
	{
		path: "/blog",
		name: "blog",
		component: Blog,
	},
	{
		path: "/blog/:slug",
		name: "blog-post",
		component: BlogPost,
	},
	{
		path: "/contact",
		name: "contact",
		component: Contact,
	},
	{
		path: "/login",
		name: "login",
		component: LoginView,
	},

	/*
	|--------------------------------------------------------------------------
	| Authenticated Routes
	|--------------------------------------------------------------------------
	*/

	{
		path: "/dashboard",
		name: "dashboard",
		component: () => import("@/Pages/Dashboard.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	/*
	|--------------------------------------------------------------------------
	| Artists Admin
	|--------------------------------------------------------------------------
	*/

	{
		path: "/admin/artists",
		name: "admin.artists",
		component: () => import("@/Pages/Admin/Artists/Index.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	{
		path: "/admin/artists/:id/edit",
		name: "admin.artists.edit",
		component: () => import("@/Pages/Admin/Artists/Edit.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	/*
	|--------------------------------------------------------------------------
	| Gigs Admin
	|--------------------------------------------------------------------------
	*/

	{
		path: "/admin/gigs",
		name: "admin.gigs",
		component: () => import("@/Pages/Admin/Gigs/Index.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	{
		path: "/admin/gigs/:id/edit",
		name: "admin.gigs.edit",
		component: () => import("@/Pages/Admin/Gigs/Edit.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	/*
	|--------------------------------------------------------------------------
	| Blog Admin
	|--------------------------------------------------------------------------
	*/

	{
		path: "/admin/blog",
		name: "admin.blog",
		component: () => import("@/Pages/Admin/Blogs/Index.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	{
		path: "/admin/blog/:id/edit",
		name: "admin.blog.edit",
		component: () => import("@/Pages/Admin/Blogs/Edit.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	/*
	|--------------------------------------------------------------------------
	| Pages Admin
	|--------------------------------------------------------------------------
	*/

	{
		path: "/admin/pages",
		name: "admin.pages",
		component: () => import("@/Pages/Admin/Pages/Index.vue"),
		meta: {
			layout: "authenticated",
		},
	},

	{
		path: "/admin/pages/:id/edit",
		name: "admin.pages.edit",
		component: () => import("@/Pages/Admin/Pages/Edit.vue"),
		meta: {
			layout: "authenticated",
		},
	},
];

const router = createRouter({
	history: createWebHistory("/"),
	routes,
});

router.onError((error) => {
	const message = String(error?.message ?? "");
	const isChunkLoadError =
		message.includes("Failed to fetch dynamically imported module") ||
		message.includes("Importing a module script failed");

	if (!isChunkLoadError) {
		return;
	}

	const reloadKey = "pp:stale-chunk-reload";

	if (sessionStorage.getItem(reloadKey) === "1") {
		sessionStorage.removeItem(reloadKey);

		return;
	}

	sessionStorage.setItem(reloadKey, "1");
	window.location.reload();
});

export default router;
