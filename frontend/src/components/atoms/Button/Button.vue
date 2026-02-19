<template>
  <button type="button" :class="classes" @click="onClick" :style="style">
    {{ label }}
  </button>
</template>

<script>
import { computed, reactive } from "vue";

export default {
  name: "my-button",

  props: {
    label: {
      type: String,
      required: true,
    },
    primary: {
      type: Boolean,
      default: false,
    },
    size: {
      type: String,
      validator: function (value) {
        return ["small", "medium", "large"].indexOf(value) !== -1;
      },
    },
    backgroundColor: {
      type: String,
    },
  },

  emits: ["click"],

  setup(props, { emit }) {
    props = reactive(props);
    return {
      classes: computed(() => {
        const baseClasses = "inline-block cursor-pointer border-0 rounded-full font-bold leading-none font-sans bg-blue-500";
        
        const variantClasses = props.primary
          ? "bg-[#555ab9] text-white"
          : "bg-transparent text-gray-800 ring-1 ring-black/15";
        
        const sizeClasses = {
          small: "px-4 py-2.5 text-xs",
          medium: "px-5 py-2.5 text-sm",
          large: "px-6 py-3 text-base",
        };
        
        const size = props.size || "medium";
        
        return `${baseClasses} ${variantClasses} ${sizeClasses[size]}`;
      }),
      style: computed(() => ({
        backgroundColor: props.backgroundColor,
      })),
      onClick() {
        emit("click");
      },
    };
  },
};
</script>

