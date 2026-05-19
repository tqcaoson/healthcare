import React from 'react';

import {
    ResponsiveContainer,
    LineChart,
    Line,
    XAxis,
    YAxis,
    Tooltip
} from 'recharts';

export default function PatientVitalsChart({ data }) {

    return (
        <div className="bg-white rounded-2xl p-6 shadow-sm">

            <h2 className="text-lg font-semibold mb-5">
                Blood Pressure Trend
            </h2>

            <ResponsiveContainer width="100%" height={350}>

                <LineChart data={data}>

                    <XAxis dataKey="time" />

                    <YAxis />

                    <Tooltip />

                    <Line dataKey="systolic" />

                    <Line dataKey="diastolic" />

                </LineChart>

            </ResponsiveContainer>
        </div>
    );
}