<template>
    <div class="item flex items-center">
        <div class="card w-full px-4 py-4">
            <div class="flex justify-between text-sm text-80 mb-3">
                <h4>{{ activity.user.fullname }}</h4>
                <span class="uppercase tracking-wide">{{ activity.created_at }}</span>
            </div>
            <div class="flex items-center" v-if="activity.data">
                <span class="w-4 h-4 block rounded-full mr-3" :class="'bg-' + activity.data.status.color"></span>
                <div>
                    <h5 class="font-light" v-if="activity.data.transition">{{ activity.data.transition.title }}</h5>
                    <h3>{{ activity.data.status.title }}</h3>
                </div>
            </div>
            <p>{{ activity.comment }}</p>
        </div>
    </div>
</template>

<script>
export default {
    props: ["activity"],
    mounted() {},
};
</script>
