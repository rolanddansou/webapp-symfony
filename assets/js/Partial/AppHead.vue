<template>
  <Head :title="computeTitle">
    <title>{{computeTitle}}</title>
    <slot />
  </Head>
</template>

<script setup>
import {toRefs, computed} from "vue";
import { Head, usePage} from '@inertiajs/vue3'

const props = defineProps({
  sitename: {type: String},
  title: {type: String,},
  description: {type: String},
  thumbnail: {type: String,},
})

const {sitename, title, description, thumbnail} = toRefs(props);
const page = usePage()

const computeTitle = computed(() => {
  const siteName =
      page.props?.common?.sitename ||
      sitename.value ||
      "App";

  return title.value
      ? `${title.value} | ${siteName}`
      : siteName;
});
</script>
