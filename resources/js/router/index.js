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
import Login from "@/Components/LoginView.vue";
import Dashboard from "@/Pages/Dashboard.vue";
import PageManager from "@/Pages/PageManager.vue";
import EditPage from "@/Pages/EditPage.vue";

import AdminArtists from "@/Pages/Artists/Index.vue";
import EditArtist from "@/Pages/Artists/Edit.vue";
import AdminGigs from "@/Pages/Gigs/Index.vue";
import EditGig from "@/Pages/Gigs/Edit.vue";
import AdminBlog from "@/Pages/Blog/Index.vue";
import EditBlog from "@/Pages/Blog/Edit.vue";

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
		component: Login,
	},
	{
		path: "/dashboard",
		name: "dashboard",
		component: Dashboard,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/pages",
		name: "admin.pages",
		component: PageManager,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/pages/:id/edit",
		name: "admin.pages.edit",
		component: EditPage,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/artists",
		name: "admin.artists",
		component: AdminArtists,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/artists/:id/edit",
		name: "admin.artists.edit",
		component: EditArtist,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/gigs",
		name: "admin.gigs",
		component: AdminGigs,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/gigs/:id/edit",
		name: "admin.gigs.edit",
		component: EditGig,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/blog",
		name: "admin.blog",
		component: AdminBlog,
		meta: {
			layout: "authenticated",
		},
	},
	{
		path: "/admin/blog/:id/edit",
		name: "admin.blog.edit",
		component: EditBlog,
		meta: {
			layout: "authenticated",
		},
	},
];

const router = createRouter({
	history: createWebHistory("/"),
	routes,
});

export default router;
