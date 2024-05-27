<template>
    <div class="item flex items-center">
        <Card class="px-4 py-4 w-full">
            <div class="flex justify-between text-sm text-gray-800 mb-3">
                <h4>{{ activity.user.fullname }}</h4>
                <span class="uppercase tracking-wide text-gray-500">{{ activity.created_at }}</span>
            </div>
            <div class="flex items-center" v-if="activity.data">
                <span class="w-4 h-4 block rounded-full mr-3" :class="'bg-' + activity.data.status.color"></span>
                <div>
                    <h5 class="font-light text-gray-800" v-if="activity.data.transition">{{ activity.data.transition.title }}</h5>
                    <h3 class="text-gray-800">{{ activity.data.status.title }}</h3>
                </div>
            </div>
            <p class="text-gray-800">{{ activity.comment }}</p>
        </Card>
    </div>
</template>

<script>
export default {
    props: ["activity"],
    mounted() {},
};
</script>
